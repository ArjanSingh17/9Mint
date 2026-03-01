<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Listing;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    // Show all collections
    public function index()
    {
        $collections = Collection::approved()->whereNull('deleted_at')->get();
        return view('collections.index', compact('collections'));
    }

    // Show NFTs for a single collection
    public function show($slug)
    {
        $collection = Collection::approved()->where('slug', $slug)->firstOrFail();
        $nfts = $collection->nfts()->marketVisible()->get();

        $nftIds = $nfts->pluck('id')->all();
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

        foreach ($nfts as $nft) {
            $nft->active_listing = $listingByNftId->get($nft->id);
        }

        return view('collections.show', compact('collection', 'nfts'));
    }
}
