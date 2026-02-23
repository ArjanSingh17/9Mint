<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('users')
            ->select('wallet_address', DB::raw('COUNT(*) as aggregate'))
            ->whereNotNull('wallet_address')
            ->whereRaw("TRIM(wallet_address) <> ''")
            ->groupBy('wallet_address')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('wallet_address')
            ->all();

        if (!empty($duplicates)) {
            $sample = implode(', ', array_slice($duplicates, 0, 5));
            throw new RuntimeException(
                'Cannot add unique constraint on users.wallet_address. Duplicate values found: ' . $sample
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('wallet_address', 'users_wallet_address_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_wallet_address_unique');
        });
    }
};
