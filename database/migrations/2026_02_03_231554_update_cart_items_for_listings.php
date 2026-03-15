<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $t) {
            $t->foreignId('listing_id')->nullable()->after('user_id')->constrained('listings')->nullOnDelete();
            $t->string('selected_pay_currency', 10)->nullable()->after('quantity');
        });

        Schema::table('cart_items', function (Blueprint $t) {
            $t->dropForeign('cart_items_user_id_foreign');
            $t->dropForeign('cart_items_nft_id_foreign');
            $t->dropUnique('cart_items_user_id_nft_id_unique');
            $t->dropColumn(['nft_id']);
        });

        Schema::table('cart_items', function (Blueprint $t) {
            $t->unique(['user_id', 'listing_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $t) {
            $t->dropUnique(['user_id', 'listing_id']);
        });

        Schema::table('cart_items', function (Blueprint $t) {
            $t->foreignId('nft_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('cart_items', function (Blueprint $t) {
            $t->unique(['user_id', 'nft_id']);
            $t->dropColumn(['listing_id', 'selected_pay_currency']);
        });
    }
};
