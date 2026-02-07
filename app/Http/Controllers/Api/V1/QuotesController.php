<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\Pricing\PricingService;
use Illuminate\Http\Request;

class QuotesController extends Controller
{
    public function show(Request $request)
    {
        $data = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $listing = Listing::with('token.nft')
            ->where('id', $data['listing_id'])
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->firstOrFail();
        $currencyCatalog = app(CurrencyCatalogInterface::class);
        $payCurrency = $data['currency'] ?? $currencyCatalog->defaultDisplayCurrency();

        $quote = app(PricingService::class)->quote($listing, $payCurrency);

        return response()->json([
            'data' => [
                'listing_id' => $listing->id,
                'display_amount' => $quote['pay_amount'],
                'display_currency' => $quote['pay_currency'],
                'fx_rated_at' => $quote['fx_rated_at'],
                'fx_provider' => $quote['fx_provider'],
            ],
        ]);
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'max:200'],
            'items.*.listing_id' => ['required', 'integer', 'exists:listings,id'],
            'items.*.currency' => ['nullable', 'string', 'max:10'],
        ]);

        $items = collect($data['items']);
        if ($items->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $listingIds = $items->pluck('listing_id')->unique()->values();
        $listings = Listing::with('token.nft')
            ->whereIn('id', $listingIds)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->get()
            ->keyBy('id');

        $currencyCatalog = app(CurrencyCatalogInterface::class);
        $defaultCurrency = $currencyCatalog->defaultDisplayCurrency();
        $pricing = app(PricingService::class);

        $results = [];
        foreach ($items as $item) {
            $listing = $listings->get($item['listing_id']);
            if (! $listing) {
                continue;
            }

            $payCurrency = strtoupper($item['currency'] ?? $defaultCurrency);
            $quote = $pricing->quote($listing, $payCurrency);

            $results[] = [
                'listing_id' => $listing->id,
                'display_amount' => $quote['pay_amount'],
                'display_currency' => $quote['pay_currency'],
                'fx_rated_at' => $quote['fx_rated_at'],
                'fx_provider' => $quote['fx_provider'],
            ];
        }

        return response()->json(['data' => $results]);
    }
}
