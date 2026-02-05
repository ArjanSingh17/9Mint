<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'GBP', 'type' => 'fiat', 'decimals' => 2, 'symbol' => '£', 'is_enabled' => true],
            ['code' => 'USD', 'type' => 'fiat', 'decimals' => 2, 'symbol' => '$', 'is_enabled' => true],
            ['code' => 'EUR', 'type' => 'fiat', 'decimals' => 2, 'symbol' => '€', 'is_enabled' => true],
            ['code' => 'BTC', 'type' => 'crypto', 'decimals' => 8, 'symbol' => '₿', 'is_enabled' => true],
            ['code' => 'ETH', 'type' => 'crypto', 'decimals' => 8, 'symbol' => 'Ξ', 'is_enabled' => true],
        ];

        foreach ($currencies as $data) {
            Currency::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}
