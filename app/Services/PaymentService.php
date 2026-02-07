<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentIntent;
use App\Models\SalesHistory;
use App\Models\User;
use App\Models\Listing;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function simulatePayment(Order $order, $user, string $provider, string $outcome = 'success'): PaymentIntent
    {
        return DB::transaction(function () use ($order, $user, $provider, $outcome) {
            $walletService = app(WalletService::class);
            $platformUserId = User::where('name', '9Mint')->value('id');
            $feeRate = Listing::SERVICE_FEE_RATE;
            $intent = PaymentIntent::create([
                'order_id' => $order->id,
                'provider' => $provider,
                'status' => 'created',
                'metadata' => ['requested_at' => now()->toIso8601String()],
            ]);

            if ($outcome === 'success') {
                foreach ($order->items as $item) {
                    if ($item->listing) {
                        $item->listing->update([
                            'status' => 'sold',
                            'reserved_until' => null,
                            'reserved_by_user_id' => null,
                        ]);
                    }
                    if ($item->token) {
                        $isFirstSale = empty($item->token->first_sale_order_id);
                        $item->token->update([
                            'owner_user_id' => $user->id,
                            'status' => 'owned',
                            'first_sale_order_id' => $item->token->first_sale_order_id ?? $order->id,
                        ]);
                        if ($isFirstSale && $item->token->nft) {
                            $nft = $item->token->nft;
                            $remaining = max(0, (int) $nft->editions_remaining - 1);
                            $nft->update(['editions_remaining' => $remaining]);
                        }
                    }

                    SalesHistory::create([
                        'listing_id' => $item->listing_id,
                        'token_id' => $item->token_id,
                        'order_id' => $order->id,
                        'pay_amount' => ($item->pay_unit_amount ?? 0) * ($item->quantity ?? 1),
                        'pay_currency' => $item->pay_currency ?? ($order->pay_currency ?? 'GBP'),
                        'sold_at' => now(),
                    ]);

                    if ($item->listing) {
                        $gross = (float) ($item->pay_unit_amount ?? 0) * (float) ($item->quantity ?? 1);
                        $currency = $item->pay_currency ?? ($order->pay_currency ?? 'GBP');
                        $sellerId = $item->listing->seller_user_id;

                        if ($platformUserId && $sellerId === $platformUserId) {
                            $walletService->credit($platformUserId, $currency, $gross, [
                                'order_id' => $order->id,
                                'listing_id' => $item->listing_id,
                                'metadata' => ['source' => 'sale'],
                            ]);
                        } else {
                            $sellerNet = $gross * (1 - $feeRate);
                            $platformFee = $gross * $feeRate;

                            if ($sellerId) {
                                $walletService->credit($sellerId, $currency, $sellerNet, [
                                    'order_id' => $order->id,
                                    'listing_id' => $item->listing_id,
                                    'metadata' => ['source' => 'sale'],
                                ]);
                            }

                            if ($platformUserId) {
                                $walletService->credit($platformUserId, $currency, $platformFee, [
                                    'order_id' => $order->id,
                                    'listing_id' => $item->listing_id,
                                    'metadata' => ['source' => 'platform_fee'],
                                ]);
                            }
                        }
                    }
                }

                $order->update(['status' => 'paid']);
                $intent->update(['status' => 'captured']);
            } else {
                foreach ($order->items as $item) {
                    if ($item->listing) {
                        $item->listing->update([
                            'status' => 'active',
                            'reserved_until' => null,
                            'reserved_by_user_id' => null,
                        ]);
                    }
                }

                $order->update(['status' => 'failed']);
                $intent->update(['status' => 'failed']);
            }

            return $intent->fresh();
        });
    }
}
