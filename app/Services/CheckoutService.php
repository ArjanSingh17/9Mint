<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Pricing\PricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(private PricingService $pricing) {}

    public function createOrderFromCart($user, string $payCurrency, ?string $checkoutToken = null): Order
    {
        return DB::transaction(function () use ($user, $payCurrency, $checkoutToken) {
            $cartItems = CartItem::where('user_id', $user->id)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                abort(422, 'Cart is empty');
            }

            $listingIds = $cartItems->pluck('listing_id')->all();
            $listings = Listing::with('token')
                ->whereIn('id', $listingIds)
                ->lockForUpdate()
                ->get();

            if ($listings->count() !== count($listingIds)) {
                abort(422, 'One or more listings are no longer available');
            }

            $now = now();
            $reserveUntil = $now->copy()->addMinutes(10);

            foreach ($listings as $listing) {
                $isReservedByUser = $listing->status === 'reserved'
                    && $listing->reserved_by_user_id === $user->id
                    && $listing->reserved_until
                    && $listing->reserved_until->isFuture();

                if ($listing->status !== 'active' && ! $isReservedByUser) {
                    abort(422, 'Listing is not available');
                }

                $listing->update([
                    'status' => 'reserved',
                    'reserved_until' => $reserveUntil,
                    'reserved_by_user_id' => $user->id,
                ]);
            }

            $quote = $this->pricing->lockQuote($listings->all(), $payCurrency);

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'fx_rate_snapshot_id' => $quote['fx_rate_snapshot_id'] ?? null,
                'pay_currency' => $quote['pay_currency'],
                'pay_total_amount' => $quote['pay_total_amount'],
                'ref_currency' => $quote['ref_currency'],
                'ref_total_amount' => $quote['ref_total_amount'],
                'fx_provider' => $quote['fx_provider'],
                'fx_rate' => $quote['fx_rate'],
                'fx_rated_at' => $quote['fx_rated_at'],
                'expires_at' => $reserveUntil,
                'placed_at' => $now,
                'checkout_token' => $checkoutToken ?: (string) Str::uuid(),
            ]);

            foreach ($cartItems as $cartItem) {
                if ($cartItem->quantity !== 1) {
                    abort(422, 'Listings can only be purchased in quantity 1');
                }

                $listing = $listings->firstWhere('id', $cartItem->listing_id);
                $itemQuote = $quote['items'][$listing->id] ?? null;
                if (! $itemQuote) {
                    abort(422, 'Unable to quote listing');
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'listing_id' => $listing->id,
                    'token_id' => $listing->token_id,
                    'quantity' => 1,
                    'ref_unit_amount' => $itemQuote['ref_unit_amount'],
                    'ref_currency' => $itemQuote['ref_currency'],
                    'pay_unit_amount' => $itemQuote['pay_unit_amount'],
                    'pay_currency' => $itemQuote['pay_currency'],
                ]);
            }

            return $order->load('items');
        });
    }
}
