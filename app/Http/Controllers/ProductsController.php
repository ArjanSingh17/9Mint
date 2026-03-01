<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Listing;

class ProductsController extends Controller
{
    public function index()
    {
        $collections = Collection::approved()->whereHas('nfts', function ($q) {
            $q->marketVisible();
        })
        ->with(['nfts' => function ($q) {
            $q->marketVisible()->orderBy('id');
        }])
        ->get();

        $collectionIds = $collections->pluck('id')->all();
        $listedCountsByCollectionId = empty($collectionIds)
            ? collect()
            : Listing::query()
                ->join('nft_tokens', 'listings.token_id', '=', 'nft_tokens.id')
                ->join('nfts', 'nft_tokens.nft_id', '=', 'nfts.id')
                ->whereIn('nfts.collection_id', $collectionIds)
                ->where('listings.status', 'active')
                ->where(function ($q) {
                    $q->whereNull('listings.reserved_until')
                        ->orWhere('listings.reserved_until', '<', now());
                })
                ->groupBy('nfts.collection_id')
                ->selectRaw('nfts.collection_id, COUNT(*) as listed_count')
                ->pluck('listed_count', 'nfts.collection_id');

        foreach ($collections as $collection) {
            $collection->listed_editions_count = (int) ($listedCountsByCollectionId[$collection->id] ?? 0);
        }

        return view('products', compact('collections'));
    }
}
