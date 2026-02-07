<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'currency_code',
            'price_crypto',
            'primary_ref_amount',
            'primary_ref_currency',
        ];

        $existing = array_filter(
            $columns,
            fn ($column) => Schema::hasColumn('nfts', $column)
        );

        if (!empty($existing)) {
            Schema::table('nfts', function (Blueprint $t) use ($existing) {
                $t->dropColumn($existing);
            });
        }
    }

    public function down(): void
    {
        Schema::table('nfts', function (Blueprint $t) {
            $t->string('currency_code', 10)->default('ETH')->after('image_url');
            $t->decimal('price_crypto', 18, 8)->default(0)->after('currency_code');
            $t->decimal('primary_ref_amount', 36, 18)->nullable()->after('price_crypto');
            $t->string('primary_ref_currency', 10)->nullable()->after('primary_ref_amount');
        });
    }
};
