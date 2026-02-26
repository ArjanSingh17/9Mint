<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Nft;
use App\Models\OrderItem;
use App\Models\NftToken;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\Pricing\PricingService;

class NftController extends Controller
{
    public function show($slug)
{
    $nft = Nft::with(['collection', 'reviews.user'])
        ->where('slug', $slug)
        ->where('is_active', 1)
        ->firstOrFail();
        $collection = $nft->collection;
        $viewer = auth()->user();
        $canManage = $viewer && (
            $viewer->role === 'admin'
            || (int) $nft->submitted_by_user_id === (int) $viewer->id
            || ($collection && (int) $collection->submitted_by_user_id === (int) $viewer->id)
            || ($collection && empty($collection->submitted_by_user_id) && !empty($collection->creator_name) && $collection->creator_name === $viewer->name)
        );
        $isPubliclyVisible = $collection
            && $collection->approval_status === \App\Models\Collection::APPROVAL_APPROVED
            && (bool) $collection->is_public
            && $nft->approval_status === Nft::APPROVAL_APPROVED
            && (bool) $nft->is_active;

        if (! $isPubliclyVisible && ! $canManage) {
            abort(404);
        }

        $listing = Listing::with('seller')->whereHas('token', function ($query) use ($nft) {
            $query->where('nft_id', $nft->id);
        })
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->orderBy('ref_amount', 'asc')
            ->first();

        $listedEditionsCount = Listing::query()
            ->whereHas('token', function ($query) use ($nft) {
                $query->where('nft_id', $nft->id);
            })
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->count();

        $quotes = [];
        $currencies = [];
        if ($listing) {
            $currencyCatalog = app(CurrencyCatalogInterface::class);
            $currencies = $currencyCatalog->listEnabledCurrencies();
            $pricing = app(PricingService::class);
            foreach ($currencies as $currency) {
                try {
                    $quotes[$currency] = $pricing->quote($listing, $currency);
                } catch (\Throwable $e) {
                    // Skip unsupported currencies or provider errors.
                }
            }
        }

        if (empty($currencies)) {
            $currencies = [app(CurrencyCatalogInterface::class)->defaultDisplayCurrency()];
        }

        $ownedTokens = collect();
        $eligibleTokenIds = [];
        if (auth()->check()) {
            $user = auth()->user();

            $ownedTokens = NftToken::with('listing')
                ->where('nft_id', $nft->id)
                ->where('owner_user_id', $user->id)
                ->get();

            $eligibleTokenIds = OrderItem::whereIn('token_id', $ownedTokens->pluck('id'))
                ->whereHas('order', function ($q) use ($user) {
                    $q->where('status', 'paid')
                        ->where('user_id', $user->id);
                })
                ->pluck('token_id')
                ->unique()
                ->all();
        }
        //New code to calculate average rating and review count
         $averageRating = $nft->reviews()->avg('rating');
         $reviewCount   = $nft->reviews()->count();
         $userHasReviewed = false;

$userReview = null;

if (auth()->check()) {
    $userReview = $nft->reviews()
        ->where('user_id', auth()->id())
        ->first();
}


        return view('nfts.show', compact('nft', 'collection', 'listing', 'quotes', 'currencies', 'ownedTokens', 'eligibleTokenIds' , 'averageRating', 'reviewCount', 'userReview'));
    }
}
