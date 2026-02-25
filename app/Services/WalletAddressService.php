<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;

class WalletAddressService
{
    private const MAX_GENERATION_ATTEMPTS = 10;

    public function generateUniqueAddress(): string
    {
        for ($attempt = 0; $attempt < self::MAX_GENERATION_ATTEMPTS; $attempt++) {
            $candidate = $this->generateCandidateAddress();

            $exists = User::where('wallet_address', $candidate)->exists();
            if (!$exists) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Unable to generate a unique wallet address.');
    }

    public function assignGeneratedAddress(User $user): string
    {
        if (!empty($user->wallet_address)) {
            return (string) $user->wallet_address;
        }

        for ($attempt = 0; $attempt < self::MAX_GENERATION_ATTEMPTS; $attempt++) {
            $candidate = $this->generateCandidateAddress();

            try {
                $user->wallet_address = $candidate;
                $user->save();

                return $candidate;
            } catch (QueryException $e) {
                // Unique constraint collision: retry with another generated address.
                if (($e->errorInfo[0] ?? null) !== '23000') {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Unable to assign a unique wallet address.');
    }

    public function normalize(?string $address): ?string
    {
        $normalized = trim((string) $address);
        if ($normalized === '') {
            return null;
        }

        if (str_starts_with(strtolower($normalized), '0x')) {
            return strtolower($normalized);
        }

        return $normalized;
    }

    protected function generateCandidateAddress(): string
    {
        return '0x' . bin2hex(random_bytes(20));
    }
}
