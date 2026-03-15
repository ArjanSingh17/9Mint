<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\WalletAddressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletAddressServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_unique_address_has_realistic_format(): void
    {
        $service = new WalletAddressService();

        $address = $service->generateUniqueAddress();

        $this->assertMatchesRegularExpression('/^0x[a-f0-9]{40}$/', $address);
    }

    public function test_generate_unique_address_retries_when_collision_happens(): void
    {
        User::create([
            'name' => 'existing-user',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'wallet_address' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        ]);

        $service = new class extends WalletAddressService {
            private array $candidates = [
                '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                '0xbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
            ];

            protected function generateCandidateAddress(): string
            {
                return array_shift($this->candidates);
            }
        };

        $address = $service->generateUniqueAddress();

        $this->assertSame('0xbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', $address);
    }
}
