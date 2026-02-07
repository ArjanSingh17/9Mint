<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\Pricing\RateProviderInterface;
use Illuminate\Support\Facades\DB;

class WalletService
{
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
}
