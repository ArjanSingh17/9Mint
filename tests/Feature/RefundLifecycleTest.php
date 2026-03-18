<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefundLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_hold_locked_token_cannot_be_listed_or_downloaded_until_finalized(): void
    {
        [$buyer, $seller, $order, $tokenId] = $this->seedSaleData();

        app(PaymentService::class)->simulatePayment($order, $buyer, 'mock_wallet', 'success');
        $orderItem = OrderItem::query()->where('order_id', $order->id)->firstOrFail();

        $this->actingAs($buyer)
            ->post(route('inventory.listing.store'), [
                'token_id' => $tokenId,
                'ref_amount' => 99.99,
                'ref_currency' => 'GBP',
            ])
            ->assertSessionHas('error');

        $this->actingAs($buyer)
            ->get(route('inventory.token.download', ['token' => $tokenId]))
            ->assertForbidden();

        $this->assertSame(OrderItem::LIFECYCLE_HOLD_PENDING, $orderItem->fresh()->lifecycle_status);
    }

    public function test_refund_request_extends_hold_by_three_days(): void
    {
        [$buyer, $seller, $order] = $this->seedSaleData();
        app(PaymentService::class)->simulatePayment($order, $buyer, 'mock_wallet', 'success');
        $item = OrderItem::query()->where('order_id', $order->id)->firstOrFail();

        $this->actingAs($buyer)
            ->post(route('orders.refund-request', $order->id), [
                'item_ids' => [$item->id],
                'reason' => 'Not as described',
                'notes' => 'High resolution mismatch and metadata is inconsistent with listing.',
            ])
            ->assertSessionHas('status');

        $item = $item->fresh();
        $this->assertSame(OrderItem::LIFECYCLE_REFUND_REQUESTED, $item->lifecycle_status);
        $this->assertNotNull($item->hold_extended_until);
        $this->assertTrue($item->hold_extended_until->gt($item->hold_expires_at));
    }

    public function test_admin_denial_requires_reason_and_approval_cancels_settlement(): void
    {
        [$buyer, $seller, $order] = $this->seedSaleData();
        app(PaymentService::class)->simulatePayment($order, $buyer, 'mock_wallet', 'success');
        $item = OrderItem::query()->where('order_id', $order->id)->firstOrFail();

        $this->actingAs($buyer)
            ->post(route('orders.refund-request', $order->id), [
                'item_ids' => [$item->id],
                'reason' => 'Wrong content',
                'notes' => 'Details supplied for manual review.',
            ])
            ->assertSessionHas('status');

        $admin = User::create([
            'name' => 'admin-user',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.refunds.deny', $item->id), [])
            ->assertSessionHasErrors('reason');

        $this->actingAs($admin)
            ->post(route('admin.refunds.approve', $item->id))
            ->assertSessionHas('status');

        $item = $item->fresh();
        $this->assertSame(OrderItem::LIFECYCLE_REFUND_APPROVED, $item->lifecycle_status);
        $this->assertDatabaseHas('sales_histories', [
            'order_id' => $order->id,
            'token_id' => $item->token_id,
            'settlement_status' => 'cancelled',
        ]);
    }

    private function seedSaleData(): array
    {
        $platform = User::create([
            'name' => '9Mint',
            'email' => 'platform@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'wallet_address' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        ]);

        $buyer = User::create([
            'name' => 'buyer-user',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'wallet_address' => '0xbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
        ]);

        $seller = User::create([
            'name' => 'seller-user',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'wallet_address' => '0xcccccccccccccccccccccccccccccccccccccccc',
        ]);

        DB::table('collections')->insert([
            'slug' => 'test-collection',
            'name' => 'Test Collection',
            'description' => 'Test',
            'cover_image_url' => '/images/test.png',
            'creator_name' => $seller->name,
            'submitted_by_user_id' => $seller->id,
            'approval_status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $collectionId = (int) DB::table('collections')->where('slug', 'test-collection')->value('id');

        DB::table('nfts')->insert([
            'collection_id' => $collectionId,
            'slug' => 'test-nft',
            'name' => 'Test NFT',
            'description' => 'NFT for tests',
            'image_url' => '/images/test-nft.png',
            'thumbnail_url' => '/images/test-nft.png',
            'currency_code' => 'ETH',
            'price_crypto' => 1,
            'primary_ref_amount' => 100,
            'primary_ref_currency' => 'GBP',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => true,
            'approval_status' => 'approved',
            'submitted_by_user_id' => $seller->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $nftId = (int) DB::table('nfts')->where('slug', 'test-nft')->value('id');

        DB::table('orders')->insert([
            'user_id' => $buyer->id,
            'status' => 'pending',
            'pay_currency' => 'GBP',
            'pay_total_amount' => 100,
            'ref_currency' => 'GBP',
            'ref_total_amount' => 100,
            'placed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $orderId = (int) DB::table('orders')->max('id');

        DB::table('nft_tokens')->insert([
            'nft_id' => $nftId,
            'serial_number' => 1,
            'owner_user_id' => $seller->id,
            'first_sale_order_id' => null,
            'status' => 'owned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tokenId = (int) DB::table('nft_tokens')->max('id');

        DB::table('listings')->insert([
            'token_id' => $tokenId,
            'seller_user_id' => $seller->id,
            'status' => 'active',
            'ref_amount' => 100,
            'ref_currency' => 'GBP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $listingId = (int) DB::table('listings')->max('id');

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'listing_id' => $listingId,
            'token_id' => $tokenId,
            'quantity' => 1,
            'ref_unit_amount' => 100,
            'ref_currency' => 'GBP',
            'pay_unit_amount' => 100,
            'pay_currency' => 'GBP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = Order::with('items.listing.token')->findOrFail($orderId);

        return [$buyer, $seller, $order, $tokenId, $platform];
    }
}
