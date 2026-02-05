<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Order};
use App\Services\CheckoutService;
use App\Services\Pricing\CurrencyCatalogInterface;

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
            $user = request()->user();

    $orders = Order::with(['items.listing.token.nft'])
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
            'pay_currency' => 'nullable|string|max:10',
        ]);

        if (!empty($data['checkout_token'])) {
            $existing = Order::where('checkout_token', $data['checkout_token'])->first();
            if ($existing) {
                return response()->json(['data' => $existing->load('items.listing.token.nft')], 200);
            }
        }

        $currencyCatalog = app(CurrencyCatalogInterface::class);
        $payCurrency = $data['pay_currency'] ?? $currencyCatalog->defaultPayCurrency();

        $order = app(CheckoutService::class)->createOrderFromCart($user, $payCurrency, $data['checkout_token'] ?? null);

        return response()->json(['data' => $order->load('items.listing.token.nft')], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
         $user = $request->user();

    $order = Order::with(['items.listing.token.nft'])
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
                if ($item->listing) {
                    $item->listing->update([
                        'status' => 'active',
                        'reserved_until' => null,
                        'reserved_by_user_id' => null,
                    ]);
                }
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
    abort(405, 'Order updates are not supported');
}
}
