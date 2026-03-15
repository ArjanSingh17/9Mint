<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nfts', function (Blueprint $t) {
            if (!Schema::hasColumn('nfts', 'primary_ref_amount')) {
                if (Schema::hasColumn('nfts', 'price_crypto')) {
                    $t->decimal('primary_ref_amount', 36, 18)->nullable()->after('price_crypto');
                } else {
                    $t->decimal('primary_ref_amount', 36, 18)->nullable();
                }
            }

            if (!Schema::hasColumn('nfts', 'primary_ref_currency')) {
                $t->string('primary_ref_currency', 10)->nullable()->after('primary_ref_amount');
            }
        });

        Schema::table('nfts', function (Blueprint $t) {
            $drop = array_filter(
                ['price_small_gbp', 'price_medium_gbp', 'price_large_gbp'],
                fn ($column) => Schema::hasColumn('nfts', $column)
            );

            if (!empty($drop)) {
                $t->dropColumn($drop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('nfts', function (Blueprint $t) {
            $t->decimal('price_small_gbp', 10, 2)->default(29.99)->after('price_crypto');
            $t->decimal('price_medium_gbp', 10, 2)->default(39.99)->after('price_small_gbp');
            $t->decimal('price_large_gbp', 10, 2)->default(49.99)->after('price_medium_gbp');
        });

        Schema::table('nfts', function (Blueprint $t) {
            $t->dropColumn(['primary_ref_amount', 'primary_ref_currency']);
        });
    }
};
