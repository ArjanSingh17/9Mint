<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $t) {
            $t->foreignId('nft_id')->nullable()->after('listing_id')->constrained()->nullOnDelete();
            $t->index('nft_id');
        });

        DB::table('cart_items')
            ->join('listings', 'cart_items.listing_id', '=', 'listings.id')
            ->join('nft_tokens', 'listings.token_id', '=', 'nft_tokens.id')
            ->update(['cart_items.nft_id' => DB::raw('nft_tokens.nft_id')]);
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $t) {
            $t->dropIndex(['nft_id']);
            $t->dropForeign(['nft_id']);
            $t->dropColumn(['nft_id']);
        });
    }
};
