<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\UserNotificationService;
use Illuminate\Http\Request;

class OrderActionController extends Controller
{
    public function showRefundForm(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $items = OrderItem::query()
            ->with('token.nft')
            ->where('order_id', $order->id)
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('orders.index')->with('error', 'No items found on this order.');
        }

        $eligibleItemIds = $items
            ->filter(fn (OrderItem $item) => $this->canRequestRefund($item))
            ->pluck('id')
            ->all();

        return view('orders.refund-form', [
            'order' => $order,
            'items' => $items,
            'eligibleItemIds' => $eligibleItemIds,
        ]);
    }

    public function requestRefund(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);

        $data = $request->validate([
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer'],
            'reason' => ['required', 'string', 'max:120'],
            'notes' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        $items = OrderItem::query()
            ->where('order_id', $order->id)
            ->whereIn('id', $data['item_ids'])
            ->get();

        $eligibleItems = $items->filter(fn (OrderItem $item) => $this->canRequestRefund($item));
        if ($eligibleItems->isEmpty()) {
            return redirect()->route('orders.index')->with('error', 'None of the selected NFTs are eligible for refund.');
        }

        foreach ($eligibleItems as $item) {
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
        }

        return redirect()->route('orders.index')->with(
            'status',
            'Refund request submitted for ' . $eligibleItems->count() . ' NFT(s). Hold is extended by 3 days while admin review is pending.'
        );
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403, 'You cannot modify this order.');
    }

    private function canRequestRefund(OrderItem $item): bool
    {
        return $this->isWithinRefundWindow($item)
            && in_array($item->lifecycle_status, [
                OrderItem::LIFECYCLE_HOLD_PENDING,
                OrderItem::LIFECYCLE_REFUND_DENIED,
            ], true);
    }

    private function isWithinRefundWindow(OrderItem $item): bool
    {
        if (! $item->hold_expires_at) {
            return false;
        }

        return now()->lte($item->hold_expires_at);
    }
}
