<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function pay(Request $request, string $orderId)
    {
        $user = $request->user();
        $data = $request->validate([
            'provider' => ['required', 'string', 'max:32'],
            'outcome' => ['nullable', 'in:success,fail'],
        ]);

        $order = Order::with('items.listing.token')
            ->where('id', $orderId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($order->status !== 'pending') {
            abort(422, 'Order is not payable');
        }

        if ($order->expires_at && $order->expires_at->isPast()) {
            $order->update(['status' => 'expired']);
            abort(422, 'Checkout expired');
        }

        $outcome = $data['outcome'] ?? 'success';

        $intent = app(PaymentService::class)->simulatePayment($order, $user, $data['provider'], $outcome);

        return response()->json(['data' => $intent]);
    }
}
