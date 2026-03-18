<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CheckoutService;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\UserNotificationService;

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

public function requestRefund(Request $request, string $itemId)
{
    $user = $request->user();
    $item = OrderItem::query()
        ->with('order')
        ->whereKey($itemId)
        ->firstOrFail();

    if ((int) ($item->order?->user_id ?? 0) !== (int) $user->id) {
        abort(403, 'You cannot modify this order item');
    }

    if (! $item->hold_expires_at || now()->gt($item->hold_expires_at)) {
        abort(422, 'Standard refund window has expired');
    }

    if (! in_array($item->lifecycle_status, [
        OrderItem::LIFECYCLE_HOLD_PENDING,
        OrderItem::LIFECYCLE_REFUND_DENIED,
    ], true)) {
        abort(422, 'Order item is not eligible for refund request');
    }

    $data = $request->validate([
        'reason' => ['required', 'string', 'max:120'],
        'notes' => ['required', 'string', 'min:10', 'max:4000'],
    ]);

    $releaseBase = $item->holdReleaseAt() ?? $item->hold_expires_at ?? now();
    $item->update([
        'lifecycle_status' => OrderItem::LIFECYCLE_REFUND_REQUESTED,
        'refund_requested_at' => now(),
        'refund_reason' => trim($data['reason']),
        'refund_notes' => trim($data['notes']),
        'refund_denial_reason' => null,
        'refund_decided_at' => null,
        'refund_decided_by_user_id' => null,
        'hold_extended_until' => $releaseBase->copy()->addDays(3),
    ]);

    app(UserNotificationService::class)->notifyAdmins(
        'refund_requested',
        'New refund request',
        'Order item #' . $item->id . ' has a new refund request.',
        ['order_item_id' => $item->id, 'order_id' => $item->order_id]
    );

    return response()->json(['data' => $item->fresh()]);
}

public function requestInvestigation(Request $request, string $itemId)
{
    $user = $request->user();
    $item = OrderItem::query()
        ->with('order')
        ->whereKey($itemId)
        ->firstOrFail();

    if ((int) ($item->order?->user_id ?? 0) !== (int) $user->id) {
        abort(403, 'You cannot modify this order item');
    }

    $data = $request->validate([
        'notes' => ['required', 'string', 'min:10', 'max:4000'],
    ]);

    $item->update([
        'lifecycle_status' => OrderItem::LIFECYCLE_INVESTIGATION_REQUESTED,
        'investigation_requested_at' => now(),
        'refund_notes' => trim($data['notes']),
    ]);

    app(UserNotificationService::class)->notifyAdmins(
        'investigation_requested',
        'Post-window investigation requested',
        'Order item #' . $item->id . ' was escalated for investigation.',
        ['order_item_id' => $item->id, 'order_id' => $item->order_id]
    );

    return response()->json(['data' => $item->fresh()]);
}
}
