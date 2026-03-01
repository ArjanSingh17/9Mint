<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutVite;

class WalletAddressProfileAndHeaderTest extends TestCase
{
    use RefreshDatabase;
    use WithoutVite;

    public function test_profile_update_rejects_duplicate_wallet_address(): void
    {
        User::create([
            'name' => 'wallet-owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'wallet_address' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        ]);

        $user = User::create([
            'name' => 'target-user',
            'email' => 'target@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'target-user',
            'email' => 'target@example.com',
            'wallet_address' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'nfts_public' => 1,
        ]);

        $response->assertSessionHasErrors('wallet_address');
    }

    public function test_profile_update_accepts_unique_wallet_address_and_normalizes_it(): void
    {
        $user = User::create([
            'name' => 'update-user',
            'email' => 'update@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'update-user',
            'email' => 'update@example.com',
            'wallet_address' => '  0xABCDEF1234567890ABCDEF1234567890ABCDEF12  ',
            'nfts_public' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'wallet_address' => '0xabcdef1234567890abcdef1234567890abcdef12',
        ]);
    }

    public function test_linked_wallet_shows_header_switcher_with_zero_balances(): void
    {
        
        $user = User::create([
            'name' => 'header-wallet-user',
            'email' => 'header-wallet@example.com',
            'password' => bcrypt('password'),
            'wallet_address' => '0xabcdefabcdefabcdefabcdefabcdefabcdefabcd',
        ]);

        $response = $this->actingAs($user)->get('/pricing');

        $response->assertOk();
        $response->assertSee('Wallet');
        $response->assertSee('data-wallet-currency', false);
        $response->assertSee('data-net="0"', false);
    }
}
