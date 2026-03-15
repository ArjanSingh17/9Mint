<?php

return [
    'default_display_currency' => 'GBP',
    'default_pay_currency' => 'GBP',
    'enabled_currencies' => ['GBP', 'USD', 'EUR', 'ETH', 'BTC'],
    'crypto_currencies' => ['BTC', 'ETH'],
    'currency_symbols' => [
        'GBP' => '£',
        'USD' => '$',
        'EUR' => '€',
        'BTC' => '₿',
        'ETH' => 'Ξ',
    ],
    'fx_cache_ttl_seconds' => 60,
    'fx_verify_ssl' => env('FX_VERIFY_SSL', true),
];
