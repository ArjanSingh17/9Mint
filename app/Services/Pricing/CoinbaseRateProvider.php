<?php

namespace App\Services\Pricing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FxRateSnapshot;

class CoinbaseRateProvider implements RateProviderInterface
{
    private const PROVIDER = 'coinbase';

    public function getRates(string $baseCurrency): array
    {
        $baseCurrency = strtoupper($baseCurrency);
        $cacheKey = 'fx_rates_' . $baseCurrency;
        $ttl = (int) config('pricing.fx_cache_ttl_seconds', 60);

        $cached = Cache::get($cacheKey);
        if ($cached && isset($cached['rates'])) {
            return $cached;
        }

        try {
            $response = Http::timeout(5)
                ->withOptions(['verify' => (bool) config('pricing.fx_verify_ssl', true)])
                ->get('https://api.coinbase.com/v2/exchange-rates', [
                'currency' => $baseCurrency,
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Coinbase rate response failed');
            }

            $payload = $response->json();
            $rates = $payload['data']['rates'] ?? [];
            $ratedAt = now()->toIso8601String();

            $result = [
                'base' => $baseCurrency,
                'provider' => self::PROVIDER,
                'rated_at' => $ratedAt,
                'rates' => $rates,
            ];

            $snapshot = FxRateSnapshot::create([
                'base_currency' => $baseCurrency,
                'rates_json' => $rates,
                'provider' => self::PROVIDER,
                'rated_at' => $ratedAt,
            ]);

            $result['snapshot_id'] = $snapshot->id;

            Cache::put($cacheKey, $result, $ttl);
            return $result;
        } catch (\Throwable $e) {
            Log::warning('FX provider failed, using cached rates', [
                'provider' => self::PROVIDER,
                'base' => $baseCurrency,
                'error' => $e->getMessage(),
            ]);

            if ($cached) {
                return $cached;
            }

            throw $e;
        }
    }
}
