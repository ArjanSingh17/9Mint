<?php

namespace App\Services\Pricing;

interface RateProviderInterface
{
    /**
     * @return array{base:string,provider:string,rated_at:string,rates:array<string,string>}
     */
    public function getRates(string $baseCurrency): array;
}
