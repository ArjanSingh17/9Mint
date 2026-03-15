<?php

namespace App\Services\Pricing;

class ConfigCurrencyCatalog implements CurrencyCatalogInterface
{
    public function listEnabledCurrencies(): array
    {
        return config('pricing.enabled_currencies', ['GBP']);
    }

    public function isCrypto(string $currency): bool
    {
        $currency = strtoupper($currency);
        $cryptos = config('pricing.crypto_currencies', []);
        return in_array($currency, $cryptos, true);
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
