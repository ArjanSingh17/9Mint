<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Listing;
use Illuminate\Http\Request;

class CollectionPageController extends Controller
{
    public function show($slug)
    {
        $collection = Collection::query()->where('slug', $slug)->firstOrFail();
        $viewer = auth()->user();
        $canManage = $viewer && (
            $viewer->role === 'admin'
            || (int) $collection->submitted_by_user_id === (int) $viewer->id
            || (empty($collection->submitted_by_user_id) && !empty($collection->creator_name) && $collection->creator_name === $viewer->name)
        );
        $isPubliclyVisible = $collection->approval_status === Collection::APPROVAL_APPROVED
            && (bool) $collection->is_public;

        if (! $isPubliclyVisible && ! $canManage) {
            abort(404);
        }

        $nfts = $isPubliclyVisible
            ? $collection->nfts()->marketVisible()->get()
            : $collection->nfts()->get();

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
        $listedCountByNftId = $activeListings->groupBy('nft_id')->map(fn ($rows) => $rows->count());

        foreach ($nfts as $nft) {
            $nft->active_listing = $listingByNftId->get($nft->id);
            $nft->listed_editions_count = (int) ($listedCountByNftId->get($nft->id, 0));
        }

        return view('collections.show', compact('collection', 'nfts'));
    }
}
