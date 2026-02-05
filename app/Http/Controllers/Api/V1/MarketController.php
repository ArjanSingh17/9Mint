<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\SalesHistory;
use App\Models\Nft;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\Pricing\PricingService;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function market(Request $request, string $slug)
    {
        $currencyCatalog = app(CurrencyCatalogInterface::class);
        $displayCurrency = strtoupper($request->query('currency', $currencyCatalog->defaultDisplayCurrency()));
        $range = $request->query('range', 'month');

        $nft = Nft::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $listings = Listing::with(['token.nft', 'seller'])
            ->whereHas('token', function ($query) use ($nft) {
                $query->where('nft_id', $nft->id);
            })
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->get();

        $pricing = app(PricingService::class);

        $listingRows = $listings->map(function ($listing) use ($pricing, $displayCurrency) {
            $quote = $pricing->quote($listing, $displayCurrency);
            $sellerName = $listing->seller?->name ?: 'Unknown';
            if ($listing->seller?->email === 'platform@9mint.local') {
                $sellerName = '9Mint';
            }

            return [
                'listing_id' => $listing->id,
                'token_id' => $listing->token_id,
                'seller' => $sellerName,
                'seller_user_id' => $listing->seller_user_id,
                'price' => $quote['pay_amount'],
                'currency' => $quote['pay_currency'],
                'ref_amount' => (float) $listing->ref_amount,
                'ref_currency' => $listing->ref_currency,
            ];
        })->values();

        $history = $this->buildHistory($nft, $displayCurrency, $range);

        return response()->json([
            'data' => [
                'nft' => ['id' => $nft->id, 'slug' => $nft->slug, 'name' => $nft->name],
                'currency' => $displayCurrency,
                'history' => $history,
                'listings' => $listingRows,
            ],
        ]);
    }

    public function history(Request $request, string $slug)
    {
        $currencyCatalog = app(CurrencyCatalogInterface::class);
        $displayCurrency = strtoupper($request->query('currency', $currencyCatalog->defaultDisplayCurrency()));
        $range = $request->query('range', 'month');

        $nft = Nft::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json([
            'data' => [
                'nft' => ['id' => $nft->id, 'slug' => $nft->slug],
                'currency' => $displayCurrency,
                'history' => $this->buildHistory($nft, $displayCurrency, $range),
            ],
        ]);
    }

    private function buildHistory(Nft $nft, string $displayCurrency, string $range): array
    {
        $days = match ($range) {
            'week' => 7,
            'month' => 30,
            'lifetime' => 365,
            default => 30,
        };

        $from = now()->subDays($days)->startOfDay();
        $tokenIds = $nft->tokens()->pluck('id');

        $historyRows = SalesHistory::whereIn('token_id', $tokenIds)
            ->where('sold_at', '>=', $from)
            ->orderBy('sold_at')
            ->get();

        if ($historyRows->isEmpty()) {
            return [];
        }

        $pricing = app(PricingService::class);
        $grouped = $historyRows->groupBy(function ($row) {
            return $row->sold_at->toDateString();
        });

        $catalog = app(CurrencyCatalogInterface::class);
        $decimals = $catalog->isCrypto($displayCurrency) ? 8 : 2;
        $points = [];
        foreach ($grouped as $date => $rows) {
            $values = $rows->map(function ($row) use ($pricing, $displayCurrency) {
                $listing = new Listing([
                    'ref_amount' => $row->pay_amount,
                    'ref_currency' => $row->pay_currency,
                ]);
                $quote = $pricing->quote($listing, $displayCurrency);
                return (float) $quote['pay_amount'];
            })->sort()->values();

            $count = $values->count();
            if ($count === 0) {
                continue;
            }
            $middle = intdiv($count, 2);
            $median = $count % 2 === 0
                ? ($values[$middle - 1] + $values[$middle]) / 2
                : $values[$middle];

            $points[] = [
                'date' => $date,
                'value' => round($median, $decimals),
            ];
        }

        return $points;
    }
}
