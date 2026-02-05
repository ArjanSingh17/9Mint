<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;

class FavouritePageController extends Controller
{
    public function index()
    {
        // Get the logged-in user
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        // Get their favourite NFTs (using the relationship we checked earlier)
        $favourites = $user->favourites()->get();

        $nftIds = $favourites->pluck('id')->all();
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

        foreach ($favourites as $nft) {
            $nft->active_listing = $listingByNftId->get($nft->id);
        }

        // Load the view and pass the data
        return view('favourites.index', compact('favourites'));
    }
}