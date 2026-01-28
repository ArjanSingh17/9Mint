<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Nft;

class HomeController extends Controller
{
    public function index()
    {
        $nfts = Nft::where('is_active', 1)->get();

        // Board NFTs: in-stock, active, not soft-deleted
        $baseQuery = Nft::where('is_active', true)
            ->where('editions_remaining', '>', 0)
            ->whereNull('deleted_at')
            ->with('collection');

        // Trending: most favourited (top 20)
        $trending = (clone $baseQuery)
            ->withCount('favouritedBy')
            ->orderByDesc('favourited_by_count')
            ->limit(20)
            ->get();

        // Random fill (up to 40 more, excluding trending IDs)
        $trendingIds = $trending->pluck('id')->toArray();
        $random = (clone $baseQuery)
            ->whereNotIn('id', $trendingIds)
            ->inRandomOrder()
            ->limit(40)
            ->get();

        // Merge, shuffle, and format for the React board
        $boardNfts = $trending->merge($random)->shuffle()->map(function ($nft) {
            // Fallbacks ensure the board shows prices even if existing DB rows
            // have NULL size prices (can happen after adding new columns).
            $small = $nft->price_small_gbp;
            $medium = $nft->price_medium_gbp;
            $large = $nft->price_large_gbp;

            if ($small === null || (float) $small <= 0) $small = 29.99;
            if ($medium === null || (float) $medium <= 0) $medium = 39.99;
            if ($large === null || (float) $large <= 0) $large = 49.99;

            return [
                'id' => $nft->id,
                'name' => $nft->name,
                'image_url' => $nft->image_url,
                'editions_remaining' => $nft->editions_remaining,
                'editions_total' => $nft->editions_total,
                'prices' => [
                    'small' => $small,
                    'medium' => $medium,
                    'large' => $large,
                ],
                'currency' => 'GBP',
                'collection_slug' => $nft->collection?->slug,
                'collection_name' => $nft->collection?->name,
                'collection_url' => $nft->collection
                    ? route('collections.show', ['slug' => $nft->collection->slug])
                    : null,
            ];
        })->values();

        return view('homepage', compact('nfts', 'boardNfts'));
    }
}
