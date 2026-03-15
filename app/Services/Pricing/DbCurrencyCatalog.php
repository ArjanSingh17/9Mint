<?php

namespace App\Services\Pricing;

use App\Models\Currency;

class DbCurrencyCatalog implements CurrencyCatalogInterface
{
    public function listEnabledCurrencies(): array
    {
        $currencies = Currency::where('is_enabled', true)
            ->orderBy('code')
            ->pluck('code')
            ->all();
        if (empty($currencies)) {
            return config('pricing.enabled_currencies', ['GBP']);
        }
        return $currencies;
    }

    public function isCrypto(string $currency): bool
    {
        $code = strtoupper($currency);
        $type = Currency::where('code', $code)->value('type');
        if ($type === null) {
            $cryptos = config('pricing.crypto_currencies', []);
            return in_array($code, $cryptos, true);
        }
        return $type === 'crypto';
    }

    public function defaultDisplayCurrency(): string
    {
        return strtoupper(config('pricing.default_display_currency', 'GBP'));
    }

    public function defaultPayCurrency(): string
    {
        return strtoupper(config('pricing.default_pay_currency', 'GBP'));
    }
}
