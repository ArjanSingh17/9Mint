<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Listing;
use App\Services\Pricing\CurrencyCatalogInterface;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $items = CartItem::with('listing.token.nft')
            ->where('user_id', $user->id)
            ->get();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'pay_currency' => ['nullable', 'string', 'max:10'],
        ]);

        $listing = Listing::with('token')
            ->where('id', $data['listing_id'])
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->firstOrFail();

        if ($listing->seller_user_id === $user->id) {
            return response()->json(['message' => 'You cannot purchase your own NFT.'], 403);
        }

        $currencyCatalog = app(CurrencyCatalogInterface::class);
        $payCurrency = $data['pay_currency'] ?? $currencyCatalog->defaultPayCurrency();

        $item = CartItem::firstOrCreate(
            ['user_id' => $user->id, 'listing_id' => $listing->id],
            [
                'nft_id' => $listing->token?->nft_id,
                'quantity' => 1,
                'selected_pay_currency' => $payCurrency,
            ]
        );

        if (! $item->wasRecentlyCreated) {
            $item->update(['selected_pay_currency' => $payCurrency]);
        }

        return response()->json(['data' => $item->load('listing.token.nft')], 201);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $item = CartItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $item->delete();

        return response()->json(['message' => 'Item removed']);
    }
}
