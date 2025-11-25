<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{CartItem, Order, OrderItem, Nft};

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            $user = request()->user();

    $orders = Order::with(['items.nft'])
        ->where('user_id', $user->id)
        ->orderBy('placed_at', 'desc')
        ->get();

    return response()->json(['data' => $orders]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Validate optional idempotency token
        $data = $request->validate([
            'checkout_token' => 'nullable|string|max:255',
        ]);

        // Idempotency check
        if (!empty($data['checkout_token'])) {
            $existing = Order::where('checkout_token', $data['checkout_token'])->first();
            if ($existing) {
                return response()->json(['data' => $existing], 200);
            }
        }

        $order = DB::transaction(function () use ($user, $data) {

    // Lock cart items to prevent race conditions
    $cartItems = CartItem::where('user_id', $user->id)
        ->lockForUpdate()
        ->get();

    if ($cartItems->isEmpty()) {
        abort(422, 'Cart is empty');
    }

    $nftIds = $cartItems->pluck('nft_id')->all();
    $nfts = Nft::whereIn('id', $nftIds)->lockForUpdate()->get();

    $totalCrypto = 0;

    // Check stock and calculate total
    foreach ($cartItems as $ci) {
        $nft = $nfts->firstWhere('id', $ci->nft_id);
        if (!$nft || $nft->editions_remaining < $ci->quantity) {
            abort(422, 'Out of stock: ' . ($nft->name ?? 'NFT'));
        }
        $totalCrypto += (float)$nft->price_crypto * $ci->quantity;
    }

    // Create order
    $order = Order::create([
        'user_id' => $user->id,
        'status' => 'paid',
        'currency_code' => 'ETH',
        'total_crypto' => $totalCrypto,
        'total_gbp' => 0.00,
        'placed_at' => now(),
        'checkout_token' => $data['checkout_token'] ?? null,
    ]);

    // Create order items and decrement stock
    foreach ($cartItems as $ci) {
        $nft = $nfts->firstWhere('id', $ci->nft_id);
        $nft->decrement('editions_remaining', $ci->quantity);

        OrderItem::create([
            'order_id' => $order->id,
            'nft_id' => $nft->id,
            'quantity' => $ci->quantity,
            'unit_price_crypto' => $nft->price_crypto,
            'unit_price_gbp' => 0.00,
        ]);
    }

    // Clear cart
    CartItem::where('user_id', $user->id)->delete();

    return $order;
});

return response()->json(['data' => $order->load('items.nft')], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
         $user = $request->user();

    $order = Order::with(['items.nft'])
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    return response()->json(['data' => $order]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = request()->user();

        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $item->nft()->increment('editions_remaining', $item->quantity);
            }
            $order->delete();
        });

        return response()->json(['message' => 'Order deleted successfully']);
    }
    /**
 * Update the specified resource in storage.
 */
public function update(Request $request, string $id)
{
    $user = $request->user();

    $order = Order::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    $data = $request->validate([
        'items' => 'sometimes|array',
        'items.*.id' => 'required|exists:order_items,id',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    if (!empty($data['items'])) {
    DB::transaction(function () use ($order, $data) {
        foreach ($data['items'] as $itemData) {
            $item = $order->items()->where('id', $itemData['id'])->first();
            if ($item) {
                $delta = $itemData['quantity'] - $item->quantity;

                if ($delta > 0) {
                    $nft = $item->nft()->lockForUpdate()->first();
                    if ($nft->editions_remaining < $delta) {
                        abort(422, 'Not enough stock to update quantity for ' . $nft->name);
                    }
                    $nft->decrement('editions_remaining', $delta);
                } elseif ($delta < 0) {
                    // If reducing quantity, restore stock
                    $item->nft()->increment('editions_remaining', abs($delta));
                }

                $item->update(['quantity' => $itemData['quantity']]);
            }
        }

        // Recalculate order total after all items are updated
        $order->total_crypto = $order->items->sum(function ($item) {
            return $item->quantity * $item->unit_price_crypto;
        });
        $order->save();
    });
    
    // Refresh the order from database to get updated values
    $order->refresh();
}

return response()->json([
    'message' => 'Order updated successfully',
    'data' => $order->load('items.nft')
]);
}
}
