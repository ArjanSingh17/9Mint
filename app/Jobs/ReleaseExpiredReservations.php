<?php

namespace App\Jobs;

use App\Models\Listing;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReleaseExpiredReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = now();

        Listing::where('status', 'reserved')
            ->where('reserved_until', '<', $now)
            ->update([
                'status' => 'active',
                'reserved_until' => null,
                'reserved_by_user_id' => null,
            ]);

        $expiredOrders = Order::with('items.listing')
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->get();

        foreach ($expiredOrders as $order) {
            foreach ($order->items as $item) {
                if ($item->listing) {
                    $item->listing->update([
                        'status' => 'active',
                        'reserved_until' => null,
                        'reserved_by_user_id' => null,
                    ]);
                }
            }

            $order->update(['status' => 'expired']);
        }
    }
}
