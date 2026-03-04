<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Nft;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CreatorSubmissionApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_submission_requires_at_least_five_nfts(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Creator Collection',
            'description' => 'Test submission',
            'cover_image' => UploadedFile::fake()->image('cover.png'),
            'ref_currency' => 'GBP',
            'nfts' => [],
        ];

        for ($i = 0; $i < 4; $i++) {
            $payload['nfts'][] = [
                'name' => 'NFT '.$i,
                'description' => 'Description',
                'editions_total' => 1,
                'ref_amount' => 10.00,
                'image' => UploadedFile::fake()->image("nft-{$i}.png"),
            ];
        }

        $response = $this->actingAs($user)->post(route('creator.collections.store'), $payload);
        $response->assertSessionHasErrors('nfts');
    }

    public function test_creator_submission_stores_images_under_collection_folder_and_pending_state(): void
    {
        $creator = User::factory()->create();
        $response = $this->actingAs($creator)->post(route('creator.collections.store'), $this->validCreatorPayload());
        $response->assertRedirect(route('checkout.index'));

        $collection = Collection::query()
            ->where('submitted_by_user_id', $creator->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(Collection::APPROVAL_PENDING, $collection->approval_status);
        $this->assertFalse((bool) $collection->is_public);

        $folder = $collection->uploadFolderName();
        $this->assertStringStartsWith("/images/nfts/{$folder}/", (string) $collection->cover_image_url);
        $this->assertFileExists(public_path(ltrim((string) $collection->cover_image_url, '/')));

        foreach (Nft::where('collection_id', $collection->id)->get() as $nft) {
            $this->assertStringStartsWith("/images/nfts/{$folder}/", (string) $nft->image_url);
            $this->assertFileExists(public_path(ltrim((string) $nft->image_url, '/')));
        }
    }

    public function test_pending_collection_and_nft_are_visible_only_to_creator_or_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();

        $collection = Collection::create([
            'slug' => 'pending-visible-to-owner-admin',
            'name' => 'Pending Visibility',
            'submitted_by_user_id' => $creator->id,
            'creator_name' => $creator->name,
            'approval_status' => Collection::APPROVAL_PENDING,
            'is_public' => false,
        ]);

        $nft = Nft::create([
            'collection_id' => $collection->id,
            'slug' => 'pending-visible-nft',
            'name' => 'Pending Visible NFT',
            'image_url' => '/images/nfts/test/pending-visible.png',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => false,
            'submitted_by_user_id' => $creator->id,
            'approval_status' => Nft::APPROVAL_PENDING,
        ]);

        $this->get(route('collections.show', ['slug' => $collection->slug]))->assertNotFound();
        $this->get(route('nfts.show', ['slug' => $nft->slug]))->assertNotFound();

        $this->actingAs($creator)->get(route('collections.show', ['slug' => $collection->slug]))->assertOk();
        $this->actingAs($creator)->get(route('nfts.show', ['slug' => $nft->slug]))->assertOk();

        $this->actingAs($admin)->get(route('collections.show', ['slug' => $collection->slug]))->assertOk();
        $this->actingAs($admin)->get(route('nfts.show', ['slug' => $nft->slug]))->assertOk();
    }

    public function test_approving_collection_publishes_collection_and_all_child_nfts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        Wallet::create(['user_id' => $creator->id, 'currency' => 'GBP', 'balance' => 100]);

        app(WalletService::class)->placeHold($creator->id, 'GBP', 80, [
            'hold_reference' => 'hold-ref-2',
            'collection_id' => 1,
        ]);

        $collection = Collection::create([
            'slug' => 'pending-wallet-capture',
            'name' => 'Pending Wallet Capture',
            'submitted_by_user_id' => $creator->id,
            'creator_name' => $creator->name,
            'approval_status' => Collection::APPROVAL_PENDING,
            'is_public' => false,
            'creation_fee_payment_state' => 'held_wallet',
            'creation_fee_hold_reference' => 'hold-ref-2',
            'creation_fee_hold_currency' => 'GBP',
            'creation_fee_hold_amount' => 80,
            'creation_fee_refund_state' => 'none',
            'creation_fee_amount_gbp' => 80,
        ]);

        Nft::create([
            'collection_id' => $collection->id,
            'slug' => 'pending-child-1',
            'name' => 'Pending Child 1',
            'image_url' => '/images/pending-child-1.png',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => false,
            'approval_status' => Nft::APPROVAL_PENDING,
        ]);
        Nft::create([
            'collection_id' => $collection->id,
            'slug' => 'pending-child-2',
            'name' => 'Pending Child 2',
            'image_url' => '/images/pending-child-2.png',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => false,
            'approval_status' => Nft::APPROVAL_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.collections.approve', $collection))
            ->assertSessionHas('status');

        $collection->refresh();
        $this->assertSame(Collection::APPROVAL_APPROVED, $collection->approval_status);
        $this->assertTrue((bool) $collection->is_public);
        $this->assertSame('consumed', $collection->creation_fee_payment_state);
        $this->assertEquals(20.0, (float) Wallet::where('user_id', $creator->id)->where('currency', 'GBP')->value('balance'));

        $this->assertSame(
            0,
            Nft::query()
                ->where('collection_id', $collection->id)
                ->where(function ($query) {
                    $query
                        ->where('approval_status', '!=', Nft::APPROVAL_APPROVED)
                        ->orWhere('is_active', false);
                })
                ->count()
        );
    }

    public function test_rejecting_wallet_paid_collection_releases_hold_and_removes_submission_and_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        Wallet::create(['user_id' => $creator->id, 'currency' => 'GBP', 'balance' => 100]);

        app(WalletService::class)->placeHold($creator->id, 'GBP', 80, [
            'hold_reference' => 'hold-ref-1',
            'collection_id' => 1,
        ]);

        $collection = Collection::create([
            'slug' => 'pending-wallet-hold',
            'name' => 'Pending Wallet Hold',
            'submitted_by_user_id' => $creator->id,
            'creator_name' => $creator->name,
            'approval_status' => Collection::APPROVAL_PENDING,
            'is_public' => false,
            'creation_fee_payment_state' => 'held_wallet',
            'creation_fee_hold_reference' => 'hold-ref-1',
            'creation_fee_hold_currency' => 'GBP',
            'creation_fee_hold_amount' => 80,
            'creation_fee_refund_state' => 'none',
            'creation_fee_amount_gbp' => 80,
            'cover_image_url' => null,
        ]);

        $collection->update([
            'cover_image_url' => '/images/nfts/' . $collection->uploadFolderName() . '/cover.png',
        ]);

        $folderPath = public_path('images/nfts/' . $collection->uploadFolderName());
        File::ensureDirectoryExists($folderPath);
        File::put($folderPath . '/cover.png', 'test-cover');
        File::put($folderPath . '/nft-1.png', 'test-nft');

        Nft::create([
            'collection_id' => $collection->id,
            'slug' => 'pending-wallet-hold-nft',
            'name' => 'Pending Wallet Hold NFT',
            'image_url' => '/images/nfts/' . $collection->uploadFolderName() . '/nft-1.png',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => false,
            'approval_status' => Nft::APPROVAL_PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.collections.reject', $collection), [
            'reason' => 'Quality issues',
        ])->assertSessionHas('status');

        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
        $this->assertDatabaseMissing('nfts', ['collection_id' => $collection->id]);
        $this->assertDirectoryDoesNotExist($folderPath);
        $this->assertEquals(100.0, (float) Wallet::where('user_id', $creator->id)->where('currency', 'GBP')->value('balance'));
    }

    public function test_rejecting_non_wallet_paid_collection_requires_manual_refund_and_removes_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();

        $collection = Collection::create([
            'slug' => 'pending-bank-paid',
            'name' => 'Pending Bank Paid',
            'submitted_by_user_id' => $creator->id,
            'approval_status' => Collection::APPROVAL_PENDING,
            'is_public' => false,
            'creation_fee_payment_state' => 'paid_unheld',
            'creation_fee_refund_state' => 'none',
            'creation_fee_amount_gbp' => 80,
            'creation_fee_provider' => 'mock_bank',
        ]);

        Nft::create([
            'collection_id' => $collection->id,
            'slug' => 'pending-bank-paid-nft',
            'name' => 'Pending Bank Paid NFT',
            'image_url' => '/images/nfts/pending-bank-paid/nft-1.png',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => false,
            'approval_status' => Nft::APPROVAL_PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.collections.reject', $collection), [
            'reason' => 'Reject for test',
        ])->assertSessionHas('status', 'Collection rejected and removed. Manual refund is required.');

        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
        $this->assertDatabaseMissing('nfts', ['collection_id' => $collection->id]);
    }

    public function test_only_approved_collection_and_approved_nfts_are_visible(): void
    {
        $approvedCollection = Collection::create([
            'slug' => 'approved-collection',
            'name' => 'Approved Collection',
            'approval_status' => Collection::APPROVAL_APPROVED,
            'is_public' => true,
        ]);
        $pendingCollection = Collection::create([
            'slug' => 'pending-collection',
            'name' => 'Pending Collection',
            'approval_status' => Collection::APPROVAL_PENDING,
            'is_public' => false,
        ]);

        Nft::create([
            'collection_id' => $approvedCollection->id,
            'slug' => 'approved-nft',
            'name' => 'Approved NFT',
            'image_url' => '/images/a.png',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => true,
            'approval_status' => Nft::APPROVAL_APPROVED,
        ]);
        Nft::create([
            'collection_id' => $approvedCollection->id,
            'slug' => 'pending-nft',
            'name' => 'Pending NFT',
            'image_url' => '/images/p.png',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => true,
            'approval_status' => Nft::APPROVAL_PENDING,
        ]);
        Nft::create([
            'collection_id' => $pendingCollection->id,
            'slug' => 'pending-collection-approved-nft',
            'name' => 'Pending Collection Approved NFT',
            'image_url' => '/images/pca.png',
            'editions_total' => 1,
            'editions_remaining' => 1,
            'is_active' => true,
            'approval_status' => Nft::APPROVAL_APPROVED,
        ]);

        $productsResponse = $this->get(route('products.index'));
        $productsResponse->assertOk();
        $productsResponse->assertSee('Approved Collection');
        $productsResponse->assertDontSee('Pending Collection');

        $apiResponse = $this->getJson('/api/v1/nfts');
        $apiResponse->assertOk();
        $apiResponse->assertJsonFragment(['slug' => 'approved-nft']);
        $apiResponse->assertJsonMissing(['slug' => 'pending-nft']);
        $apiResponse->assertJsonMissing(['slug' => 'pending-collection-approved-nft']);
    }

    private function validCreatorPayload(): array
    {
        $payload = [
            'name' => 'Creator Collection ' . uniqid(),
            'description' => 'Test submission',
            'cover_image' => UploadedFile::fake()->image('cover.png'),
            'ref_currency' => 'GBP',
            'nfts' => [],
        ];

        for ($i = 0; $i < 5; $i++) {
            $payload['nfts'][] = [
                'name' => 'NFT '.$i,
                'description' => 'Description',
                'editions_total' => 1,
                'ref_amount' => 10.00 + $i,
                'image' => UploadedFile::fake()->image("nft-{$i}.png"),
            ];
        }

        return $payload;
    }
}
