<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill existing rows that may have NULL after adding columns.
        DB::table('nfts')
            ->whereNull('price_small_gbp')
            ->update(['price_small_gbp' => 29.99]);

        DB::table('nfts')
            ->whereNull('price_medium_gbp')
            ->update(['price_medium_gbp' => 39.99]);

        DB::table('nfts')
            ->whereNull('price_large_gbp')
            ->update(['price_large_gbp' => 49.99]);
    }

    public function down(): void
    {
        // Non-destructive: don't revert prices.
    }
};

