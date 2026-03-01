<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\Pricing\RateProviderInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    private const CREATOR_FEE_HOLD_SOURCE = 'creator_fee_hold';
    private const CREATOR_FEE_HOLD_RELEASE_SOURCE = 'creator_fee_hold_release';

    public function __construct(
        private RateProviderInterface $rateProvider,
        private CurrencyCatalogInterface $currencyCatalog
    ) {}

    public function getBalance(int $userId, string $currency): float
    {
        $wallet = Wallet::where('user_id', $userId)
            ->where('currency', strtoupper($currency))
            ->first();

        return $wallet ? (float) $wallet->balance : 0.0;
    }

    public function credit(
        int $userId,
        string $currency,
        float $amount,
        array $meta = []
    ): WalletTransaction {
        return $this->applyTransaction($userId, $currency, $amount, 'credit', $meta);
    }

    public function debit(
        int $userId,
        string $currency,
        float $amount,
        array $meta = []
    ): WalletTransaction {
        return $this->applyTransaction($userId, $currency, $amount, 'debit', $meta);
    }

    /**
     * Place a creator-fee hold by debiting wallet balance and tagging transaction metadata.
     *
     * @return array{transaction:\App\Models\WalletTransaction,hold_reference:string}
     */
    public function placeHold(
        int $userId,
        string $currency,
        float $amount,
        array $context = []
    ): array {
        $holdReference = $context['hold_reference'] ?? (string) Str::uuid();

        $existing = $this->findHoldTransaction($holdReference);
        if ($existing) {
            return [
                'transaction' => $existing,
                'hold_reference' => $holdReference,
            ];
        }

        $transaction = $this->debit($userId, $currency, $amount, [
            'order_id' => $context['order_id'] ?? null,
            'fx_provider' => $context['fx_provider'] ?? null,
            'fx_rate' => $context['fx_rate'] ?? null,
            'fx_rated_at' => $context['fx_rated_at'] ?? null,
            'metadata' => array_filter([
                'source' => self::CREATOR_FEE_HOLD_SOURCE,
                'hold_reference' => $holdReference,
                'hold_status' => 'held',
                'collection_id' => $context['collection_id'] ?? null,
                'pay_currency' => $context['pay_currency'] ?? null,
                'pay_amount' => $context['pay_amount'] ?? null,
            ], fn ($v) => !is_null($v)),
        ]);

        return [
            'transaction' => $transaction,
            'hold_reference' => $holdReference,
        ];
    }

    public function releaseHold(string $holdReference): WalletTransaction
    {
        return DB::transaction(function () use ($holdReference) {
            $holdTx = $this->findHoldTransaction($holdReference, true);
            if (! $holdTx) {
                throw new \RuntimeException('Hold transaction not found');
            }

            $holdMeta = $holdTx->metadata ?? [];
            $status = $holdMeta['hold_status'] ?? 'held';
            if ($status === 'released') {
                return $holdTx;
            }
            if ($status === 'captured') {
                throw new \RuntimeException('Hold already captured');
            }

            $creditTx = $this->credit(
                (int) $holdTx->user_id,
                (string) $holdTx->currency,
                (float) $holdTx->amount,
                [
                    'order_id' => $holdTx->order_id,
                    'fx_provider' => $holdTx->fx_provider,
                    'fx_rate' => $holdTx->fx_rate,
                    'fx_rated_at' => $holdTx->fx_rated_at,
                    'metadata' => array_filter([
                        'source' => self::CREATOR_FEE_HOLD_RELEASE_SOURCE,
                        'hold_reference' => $holdReference,
                        'collection_id' => $holdMeta['collection_id'] ?? null,
                    ], fn ($v) => !is_null($v)),
                ]
            );

            $holdMeta['hold_status'] = 'released';
            $holdMeta['released_transaction_id'] = $creditTx->id;
            $holdMeta['released_at'] = now()->toIso8601String();
            $holdTx->metadata = $holdMeta;
            $holdTx->save();

            return $holdTx->fresh();
        });
    }

    public function captureHold(string $holdReference): WalletTransaction
    {
        return DB::transaction(function () use ($holdReference) {
            $holdTx = $this->findHoldTransaction($holdReference, true);
            if (! $holdTx) {
                throw new \RuntimeException('Hold transaction not found');
            }

            $holdMeta = $holdTx->metadata ?? [];
            $status = $holdMeta['hold_status'] ?? 'held';
            if ($status === 'captured') {
                return $holdTx;
            }
            if ($status === 'released') {
                throw new \RuntimeException('Hold already released');
            }

            $holdMeta['hold_status'] = 'captured';
            $holdMeta['captured_at'] = now()->toIso8601String();
            $holdTx->metadata = $holdMeta;
            $holdTx->save();

            return $holdTx->fresh();
        });
    }

    /**
     * Convert an amount using live FX rates.
     *
     * @return array{amount:float,fx_provider:?string,fx_rate:float,fx_rated_at:?string}
     */
    public function convertAmount(float $amount, string $fromCurrency, string $toCurrency): array
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return [
                'amount' => $this->roundAmount($amount, $toCurrency, false),
                'fx_provider' => null,
                'fx_rate' => 1.0,
                'fx_rated_at' => null,
            ];
        }

        $rateTable = $this->rateProvider->getRates($fromCurrency);
        $rate = (float) ($rateTable['rates'][$toCurrency] ?? 0);
        if ($rate <= 0) {
            throw new \RuntimeException('Unsupported currency conversion');
        }

        $raw = $amount * $rate;

        return [
            'amount' => $this->roundAmount($raw, $toCurrency, true),
            'fx_provider' => $rateTable['provider'] ?? null,
            'fx_rate' => $rate,
            'fx_rated_at' => $rateTable['rated_at'] ?? null,
        ];
    }

    private function applyTransaction(
        int $userId,
        string $currency,
        float $amount,
        string $type,
        array $meta = []
    ): WalletTransaction {
        $currency = strtoupper($currency);

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Wallet amount must be positive');
        }

        return DB::transaction(function () use ($userId, $currency, $amount, $type, $meta) {
            $wallet = Wallet::where('user_id', $userId)
                ->where('currency', $currency)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                $wallet = Wallet::create([
                    'user_id' => $userId,
                    'currency' => $currency,
                    'balance' => 0,
                ]);

                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            }

            $balance = (float) $wallet->balance;

            if ($type === 'debit' && $balance < $amount) {
                throw new \RuntimeException('Insufficient wallet balance');
            }

            $wallet->balance = $type === 'credit'
                ? $balance + $amount
                : $balance - $amount;
            $wallet->save();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $userId,
                'currency' => $currency,
                'type' => $type,
                'amount' => $amount,
                'order_id' => $meta['order_id'] ?? null,
                'listing_id' => $meta['listing_id'] ?? null,
                'fx_provider' => $meta['fx_provider'] ?? null,
                'fx_rate' => $meta['fx_rate'] ?? null,
                'fx_rated_at' => $meta['fx_rated_at'] ?? null,
                'metadata' => $meta['metadata'] ?? null,
            ]);
        });
    }

    private function roundAmount(float $amount, string $currency, bool $roundUp): float
    {
        $decimals = $this->currencyCatalog->isCrypto($currency) ? 8 : 2;
        $factor = pow(10, $decimals);

        if ($roundUp) {
            return ceil($amount * $factor) / $factor;
        }

        return round($amount, $decimals);
    }

    private function findHoldTransaction(string $holdReference, bool $forUpdate = false): ?WalletTransaction
    {
        $query = WalletTransaction::query()
            ->where('type', 'debit')
            ->where('metadata->source', self::CREATOR_FEE_HOLD_SOURCE)
            ->where('metadata->hold_reference', $holdReference)
            ->latest('id');

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }
}
