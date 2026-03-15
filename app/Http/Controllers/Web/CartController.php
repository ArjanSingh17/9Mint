<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Listing;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\Pricing\PricingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $items = CartItem::with('listing.token.nft')
            ->where('user_id', $user->id)
            ->get();

        $pricing = app(PricingService::class);
        $quotes = [];
        $payCurrency = app(CurrencyCatalogInterface::class)->defaultPayCurrency();

        foreach ($items as $item) {
            $quotes[$item->id] = $pricing->quote($item->listing, $payCurrency);
        }

        return view('cart', [
            'cartItems' => $items,
            'quotes' => $quotes,
            'payCurrency' => $payCurrency,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
        ]);

        $listing = Listing::with('token')
            ->where('id', $data['listing_id'])
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->first();

        if (! $listing) {
            return back()->with('error', 'Listing is not available');
        }

        if ($listing->seller_user_id === $request->user()->id) {
            return back()->with('error', 'You cannot purchase your own NFT.');
        }

        $payCurrency = app(CurrencyCatalogInterface::class)->defaultPayCurrency();

        CartItem::firstOrCreate(
            ['user_id' => $request->user()->id, 'listing_id' => $listing->id],
            [
                'nft_id' => $listing->token?->nft_id,
                'quantity' => 1,
                'selected_pay_currency' => $payCurrency,
            ]
        );

        return back()->with('status', 'Added to basket successfully!');
    }

    public function destroy(Request $request, string $id)
    {
        $item = CartItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $item) {
            return back()->with('error', 'Item not found in basket');
        }

        $item->delete();

        return back()->with('status', 'Item removed from basket');
    }
}
