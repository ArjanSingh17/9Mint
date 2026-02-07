<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nfts', function (Blueprint $t) {
            // GBP size-based pricing used by the web shop flow (small/medium/large).
            // Defaults match the current cart pricing so existing rows remain usable.
            $t->decimal('price_small_gbp', 10, 2)->default(29.99)->after('price_crypto');
            $t->decimal('price_medium_gbp', 10, 2)->default(39.99)->after('price_small_gbp');
            $t->decimal('price_large_gbp', 10, 2)->default(49.99)->after('price_medium_gbp');
        });
    }

    public function down(): void
    {
        Schema::table('nfts', function (Blueprint $t) {
            $t->dropColumn(['price_small_gbp', 'price_medium_gbp', 'price_large_gbp']);
        });
    }
};

