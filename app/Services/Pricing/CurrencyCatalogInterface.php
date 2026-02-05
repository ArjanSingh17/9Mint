<?php

namespace App\Services\Pricing;

interface CurrencyCatalogInterface
{
    public function listEnabledCurrencies(): array;
    public function isCrypto(string $currency): bool;
    public function defaultDisplayCurrency(): string;
    public function defaultPayCurrency(): string;
}
