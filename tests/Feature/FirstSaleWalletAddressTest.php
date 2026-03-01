<?php
/*
namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FirstSaleWalletAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_sale_assigns_wallet_address_when_missing(): void
    {
        [$buyer, $seller, $order] = $this->seedSaleData(null);

        app(PaymentService::class)->simulatePayment($order, $buyer, 'mock_bank', 'success');

        $seller->refresh();

        $this->assertNotNull($seller->wallet_address);
        $this->assertMatchesRegularExpression('/^0x[a-f0-9]{40}$/', $seller->wallet_address);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $seller->id,
            'currency' => 'GBP',
        ]);
    }

    public function test_existing_wallet_address_is_not_overwritten_on_sale(): void
    {
        $existingAddress = '0x1234567890abcdef1234567890abcdef12345678';
        [$buyer, $seller, $order] = $this->seedSaleData($existingAddress);

        app(PaymentService::class)->simulatePayment($order, $buyer, 'mock_bank', 'success');

        $seller->refresh();

        $this->assertSame($existingAddress, $seller->wallet_address);
        $this->assertTrue(
            Wallet::where('user_id', $seller->id)->where('currency', 'GBP')->exists()
        );
    }

    private function seedSaleData(?string $sellerWalletAddress): array
    {
        $platform = User::create([
            'name' => '9Mint',
            'email' => 'platform@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $buyer = User::create([
            'name' => 'buyer-user',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
        ]);

        $seller = User::create([
            'name' => 'seller-user',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
            'wallet_address' => $sellerWalletAddress,
        ]);

        DB::table('collections')->insert([
            'slug' => 'test-collection',
            'name' => 'Test Collection',
            'description' => 'Test',
            'cover_image_url' => '/images/test.png',
            'creator_name' => 'tester',
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
            'currency_code' => 'ETH',
            'price_crypto' => 1,
            'primary_ref_amount' => 100,
            'primary_ref_currency' => 'GBP',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => true,
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

        return [$buyer, $seller, $order, $platform];
    }
}
*/