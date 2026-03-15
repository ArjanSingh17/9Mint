<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_histories', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('listing_id')->nullable();
            $t->unsignedBigInteger('token_id')->nullable();
            $t->unsignedBigInteger('order_id')->nullable();
            $t->decimal('pay_amount', 36, 18);
            $t->string('pay_currency', 10);
            $t->timestamp('sold_at');
            $t->timestamps();

            $t->index(['listing_id']);
            $t->index(['token_id', 'sold_at']);
            $t->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_histories');
    }
};
