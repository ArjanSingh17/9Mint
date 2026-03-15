<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_histories')) {
            return;
        }

        Schema::table('sales_histories', function (Blueprint $t) {
            $t->foreign('listing_id')
                ->references('id')
                ->on('listings')
                ->nullOnDelete();

            $t->foreign('token_id')
                ->references('id')
                ->on('nft_tokens')
                ->nullOnDelete();

            $t->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sales_histories')) {
            return;
        }

        Schema::table('sales_histories', function (Blueprint $t) {
            $t->dropForeign(['listing_id']);
            $t->dropForeign(['token_id']);
            $t->dropForeign(['order_id']);
        });
    }
};
