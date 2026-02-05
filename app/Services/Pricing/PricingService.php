<?php

namespace App\Services\Pricing;

use App\Models\Listing;

class PricingService
{
    public function __construct(
        private RateProviderInterface $rateProvider,
        private CurrencyCatalogInterface $catalog
    ) {}

    public function quote(Listing $listing, string $payCurrency): array
    {
        $refCurrency = strtoupper($listing->ref_currency);
        $payCurrency = strtoupper($payCurrency);
        $refAmount = (float) $listing->ref_amount;

        if ($refCurrency === $payCurrency) {
            return [
                'ref_amount' => $refAmount,
                'ref_currency' => $refCurrency,
                'pay_amount' => $this->roundDisplayAmount($refAmount, $payCurrency),
                'pay_currency' => $payCurrency,
                'fx_provider' => null,
                'fx_rate' => 1,
                'fx_rated_at' => null,
            ];
        }

        $rateTable = $this->rateProvider->getRates($refCurrency);
        $rate = (float) ($rateTable['rates'][$payCurrency] ?? 0);
        if ($rate <= 0) {
            throw new \RuntimeException('Unsupported currency conversion');
        }

        $payAmount = $refAmount * $rate;

        return [
            'ref_amount' => $refAmount,
            'ref_currency' => $refCurrency,
            'pay_amount' => $this->roundDisplayAmount($payAmount, $payCurrency),
            'pay_currency' => $payCurrency,
            'fx_provider' => $rateTable['provider'] ?? null,
            'fx_rate' => $rate,
            'fx_rated_at' => $rateTable['rated_at'] ?? null,
            'fx_rate_snapshot_id' => $rateTable['snapshot_id'] ?? null,
        ];
    }

    /**
     * @param Listing[] $listings
     */
    public function lockQuote(array $listings, string $payCurrency): array
    {
        if (empty($listings)) {
            throw new \InvalidArgumentException('No listings provided');
        }

        $payCurrency = strtoupper($payCurrency);
        $items = [];
        $refTotal = 0.0;
        $refCurrencies = [];
        $payTotal = 0.0;
        $fxRates = [];
        $fxProviders = [];
        $ratedAt = null;
        $snapshotId = null;

        foreach ($listings as $listing) {
            $refCurrency = strtoupper($listing->ref_currency);
            $refAmount = (float) $listing->ref_amount;
            $refCurrencies[$refCurrency] = true;
            $rateTable = $this->rateProvider->getRates($refCurrency);
            $rate = $refCurrency === $payCurrency ? 1.0 : (float) ($rateTable['rates'][$payCurrency] ?? 0);
            if ($rate <= 0) {
                throw new \RuntimeException('Unsupported currency conversion');
            }

            $payAmountRaw = $refAmount * $rate;
            $payAmount = $this->roundPayAmount($payAmountRaw, $payCurrency);

            $items[$listing->id] = [
                'ref_unit_amount' => $refAmount,
                'ref_currency' => $refCurrency,
                'pay_unit_amount' => $payAmount,
                'pay_currency' => $payCurrency,
            ];

            $refTotal += $refAmount;
            $payTotal += $payAmount;

            $fxRates[$refCurrency] = [
                $payCurrency => $rate,
            ];
            if (!empty($rateTable['provider'])) {
                $fxProviders[$rateTable['provider']] = true;
            }
            $ratedAt = $ratedAt ?? ($rateTable['rated_at'] ?? null);
            $snapshotId = $snapshotId ?? ($rateTable['snapshot_id'] ?? null);
        }

        $refCurrency = count($refCurrencies) === 1 ? array_key_first($refCurrencies) : null;
        $refTotalAmount = $refCurrency ? $refTotal : null;

        return [
            'items' => $items,
            'ref_total_amount' => $refTotalAmount,
            'ref_currency' => $refCurrency,
            'pay_total_amount' => $payTotal,
            'pay_currency' => $payCurrency,
            'fx_provider' => empty($fxProviders) ? null : implode(',', array_keys($fxProviders)),
            'fx_rate' => $fxRates,
            'fx_rated_at' => $ratedAt,
            'fx_rate_snapshot_id' => $snapshotId,
        ];
    }

    private function roundDisplayAmount(float $amount, string $currency): float
    {
        $decimals = $this->catalog->isCrypto($currency) ? 8 : 2;
        return round($amount, $decimals);
    }

    private function roundPayAmount(float $amount, string $currency): float
    {
        $decimals = $this->catalog->isCrypto($currency) ? 8 : 2;
        $factor = pow(10, $decimals);
        return ceil($amount * $factor) / $factor;
    }
}
