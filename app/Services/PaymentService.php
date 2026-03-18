<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\PaymentIntent;
use App\Models\SalesHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    private const HOLD_DAYS = 7;

    public function simulatePayment(Order $order, $user, string $provider, string $outcome = 'success'): PaymentIntent
    {
        return DB::transaction(function () use ($order, $user, $provider, $outcome) {
            $walletAddressService = app(WalletAddressService::class);
            $platformUserId = User::where('name', '9Mint')->value('id');
            $intent = PaymentIntent::create([
                'order_id' => $order->id,
                'provider' => $provider,
                'status' => 'created',
                'metadata' => ['requested_at' => now()->toIso8601String()],
            ]);

            if ($outcome === 'success') {
                $now = now();
                $holdUntil = $now->copy()->addDays(self::HOLD_DAYS);

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

                    $settlementMetadata = [];
                    if ($item->listing) {
                        $settlementMetadata = $this->buildSettlementMetadata($item, $platformUserId, $walletAddressService);
                    }

                    SalesHistory::create([
                        'listing_id' => $item->listing_id,
                        'token_id' => $item->token_id,
                        'order_id' => $order->id,
                        'pay_amount' => ($item->pay_unit_amount ?? 0) * ($item->quantity ?? 1),
                        'pay_currency' => $item->pay_currency ?? ($order->pay_currency ?? 'GBP'),
                        'sold_at' => $now,
                        'settlement_status' => SalesHistory::SETTLEMENT_PENDING,
                        'settlement_eligible_at' => $holdUntil,
                        'settlement_metadata' => $settlementMetadata,
                    ]);

                    $item->update([
                        'lifecycle_status' => OrderItem::LIFECYCLE_HOLD_PENDING,
                        'hold_expires_at' => $holdUntil,
                        'hold_extended_until' => null,
                        'refund_requested_at' => null,
                        'refund_decided_at' => null,
                        'investigation_requested_at' => null,
                        'finalized_at' => null,
                        'refund_reason' => null,
                        'refund_notes' => null,
                        'refund_denial_reason' => null,
                        'refund_decided_by_user_id' => null,
                    ]);
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

    public function finalizeMaturedSettlements(): int
    {
        $releasedCount = 0;
        $walletService = app(WalletService::class);

        $pendingSales = SalesHistory::query()
            ->where('settlement_status', SalesHistory::SETTLEMENT_PENDING)
            ->whereNotNull('settlement_eligible_at')
            ->where('settlement_eligible_at', '<=', now())
            ->orderBy('id')
            ->get();

        foreach ($pendingSales as $sale) {
            DB::transaction(function () use ($sale, $walletService, &$releasedCount) {
                /** @var SalesHistory|null $lockedSale */
                $lockedSale = SalesHistory::query()->whereKey($sale->id)->lockForUpdate()->first();
                if (! $lockedSale || $lockedSale->settlement_status !== SalesHistory::SETTLEMENT_PENDING) {
                    return;
                }

                $orderItem = OrderItem::query()
                    ->where('order_id', $lockedSale->order_id)
                    ->where('token_id', $lockedSale->token_id)
                    ->where('listing_id', $lockedSale->listing_id)
                    ->lockForUpdate()
                    ->first();

                if (! $orderItem) {
                    $lockedSale->update([
                        'settlement_status' => SalesHistory::SETTLEMENT_CANCELLED,
                        'settlement_cancelled_at' => now(),
                    ]);
                    return;
                }

                if ($orderItem->lifecycle_status === OrderItem::LIFECYCLE_REFUND_APPROVED) {
                    $lockedSale->update([
                        'settlement_status' => SalesHistory::SETTLEMENT_CANCELLED,
                        'settlement_cancelled_at' => now(),
                    ]);
                    return;
                }

                $releaseAt = $orderItem->holdReleaseAt();
                if ($releaseAt && $releaseAt->isFuture()) {
                    return;
                }

                $meta = (array) ($lockedSale->settlement_metadata ?? []);
                $txIds = [];
                foreach ((array) ($meta['credits'] ?? []) as $credit) {
                    $userId = (int) ($credit['user_id'] ?? 0);
                    $amount = (float) ($credit['amount'] ?? 0);
                    $currency = strtoupper((string) ($credit['currency'] ?? $lockedSale->pay_currency));
                    if ($userId <= 0 || $amount <= 0) {
                        continue;
                    }

                    $tx = $walletService->credit($userId, $currency, $amount, [
                        'order_id' => $lockedSale->order_id,
                        'listing_id' => $lockedSale->listing_id,
                        'metadata' => [
                            'source' => (string) ($credit['source'] ?? 'sale'),
                            'settlement_sale_history_id' => $lockedSale->id,
                        ],
                    ]);
                    $txIds[] = $tx->id;
                }

                $meta['released_transaction_ids'] = $txIds;
                $meta['released_at'] = now()->toIso8601String();

                $lockedSale->update([
                    'settlement_status' => SalesHistory::SETTLEMENT_RELEASED,
                    'settlement_released_at' => now(),
                    'settlement_metadata' => $meta,
                ]);

                $orderItem->update([
                    'lifecycle_status' => OrderItem::LIFECYCLE_FINALIZED,
                    'finalized_at' => now(),
                ]);

                foreach ((array) ($meta['credits'] ?? []) as $credit) {
                    if (($credit['source'] ?? null) !== 'sale') {
                        continue;
                    }

                    $sellerId = (int) ($credit['user_id'] ?? 0);
                    if ($sellerId <= 0) {
                        continue;
                    }

                    app(UserNotificationService::class)->notifyUser(
                        $sellerId,
                        'sale_finalized',
                        'Sale finalized',
                        'Held sale payout is now released for order item #' . $orderItem->id . '.',
                        ['order_item_id' => $orderItem->id, 'order_id' => $orderItem->order_id]
                    );
                }

                $releasedCount++;
            });
        }

        return $releasedCount;
    }

    private function buildSettlementMetadata(OrderItem $item, ?int $platformUserId, WalletAddressService $walletAddressService): array
    {
        $gross = (float) ($item->pay_unit_amount ?? 0) * (float) ($item->quantity ?? 1);
        $currency = strtoupper((string) ($item->pay_currency ?? ($item->order?->pay_currency ?? 'GBP')));
        $sellerId = (int) ($item->listing?->seller_user_id ?? 0);

        $credits = [];
        if ($platformUserId && $sellerId === $platformUserId) {
            $credits[] = [
                'user_id' => $platformUserId,
                'amount' => $gross,
                'currency' => $currency,
                'source' => 'sale',
            ];
        } else {
            $sellerNet = $gross * (1 - Listing::SERVICE_FEE_RATE);
            $platformFee = $gross * Listing::PLATFORM_FEE_RATE;
            $creatorFee = $gross * Listing::CREATOR_FEE_RATE;

            if ($sellerId > 0) {
                if ($sellerId !== $platformUserId) {
                    $seller = User::whereKey($sellerId)->lockForUpdate()->first();
                    if ($seller && empty($seller->wallet_address)) {
                        $walletAddressService->assignGeneratedAddress($seller);
                    }
                }

                $credits[] = [
                    'user_id' => $sellerId,
                    'amount' => $sellerNet,
                    'currency' => $currency,
                    'source' => 'sale',
                ];
            }

            $creatorUserId = null;
            $collection = $item->listing?->token?->nft?->collection;
            if ($collection) {
                $creatorUserId = $collection->submitted_by_user_id;
                if (! $creatorUserId && ! empty($collection->creator_name)) {
                    $creatorUserId = User::where('name', $collection->creator_name)->value('id');
                }
            }

            if ($creatorUserId && $creatorUserId !== $platformUserId) {
                $creator = User::whereKey($creatorUserId)->lockForUpdate()->first();
                if ($creator && empty($creator->wallet_address)) {
                    $walletAddressService->assignGeneratedAddress($creator);
                }

                $credits[] = [
                    'user_id' => (int) $creatorUserId,
                    'amount' => $creatorFee,
                    'currency' => $currency,
                    'source' => 'creator_fee',
                ];
            } else {
                $platformFee += $creatorFee;
            }

            if ($platformUserId) {
                $credits[] = [
                    'user_id' => $platformUserId,
                    'amount' => $platformFee,
                    'currency' => $currency,
                    'source' => 'platform_fee',
                ];
            }
        }

        return [
            'hold_days' => self::HOLD_DAYS,
            'credits' => $credits,
        ];
    }
}
