<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $t) {
            $t->foreignId('listing_id')->nullable()->after('order_id')->constrained('listings')->nullOnDelete();
            $t->foreignId('token_id')->nullable()->after('listing_id')->constrained('nft_tokens')->nullOnDelete();
            $t->decimal('ref_unit_amount', 36, 18)->nullable()->after('quantity');
            $t->string('ref_currency', 10)->nullable()->after('ref_unit_amount');
            $t->decimal('pay_unit_amount', 36, 18)->nullable()->after('ref_currency');
            $t->string('pay_currency', 10)->nullable()->after('pay_unit_amount');
        });

        Schema::table('order_items', function (Blueprint $t) {
            $t->dropForeign(['nft_id']);
            $t->dropColumn(['nft_id', 'unit_price_crypto', 'unit_price_gbp']);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $t) {
            $t->foreignId('nft_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('unit_price_crypto', 18, 8)->default(0);
            $t->decimal('unit_price_gbp', 10, 2)->default(0);
        });

        Schema::table('order_items', function (Blueprint $t) {
            $t->dropColumn([
                'listing_id',
                'token_id',
                'ref_unit_amount',
                'ref_currency',
                'pay_unit_amount',
                'pay_currency',
            ]);
        });
    }
};
