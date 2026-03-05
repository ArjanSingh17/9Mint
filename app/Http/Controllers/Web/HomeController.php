<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Nft;

class HomeController extends Controller
{
    public function index()
    {
        $likedIds = auth()->check()
            ? auth()->user()->favourites()->pluck('nfts.id')->all()
            : [];
        $nfts = Nft::marketVisible()->get();

        // Board NFTs: in-stock, active, not soft-deleted
        $baseQuery = Nft::marketVisible()
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

        $nftIds = $trending->merge($random)->pluck('id')->unique()->values();
        $activeListings = Listing::query()
            ->join('nft_tokens', 'listings.token_id', '=', 'nft_tokens.id')
            ->whereIn('nft_tokens.nft_id', $nftIds)
            ->where('listings.status', 'active')
            ->where(function ($q) {
                $q->whereNull('listings.reserved_until')
                    ->orWhere('listings.reserved_until', '<', now());
            })
            ->orderBy('listings.ref_amount', 'asc')
            ->get(['listings.*', 'nft_tokens.nft_id']);

        $listingByNftId = $activeListings->groupBy('nft_id')->map->first();

        // Merge, shuffle, and format for the React board
        $boardNfts = $trending->merge($random)->shuffle()->map(function ($nft) use ($listingByNftId, $likedIds) {
            $listing = $listingByNftId->get($nft->id);

            return [
                'id' => $nft->id,
                'name' => $nft->name,
                'image_url' => $nft->image_url,
                'editions_remaining' => $nft->editions_remaining,
                'editions_total' => $nft->editions_total,
                'listing_id' => $listing?->id,
                'price' => $listing?->ref_amount,
                'currency' => $listing?->ref_currency ?? 'GBP',
                'collection_slug' => $nft->collection?->slug,
                'collection_name' => $nft->collection?->name,
                'collection_url' => $nft->collection
                    ? route('collections.show', ['slug' => $nft->collection->slug])
                    : null,
                'is_liked' => in_array($nft->id, $likedIds, true),
            ];
        })->values();

        return view('homepage', compact('nfts', 'boardNfts'));
    }
}
