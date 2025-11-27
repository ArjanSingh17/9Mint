<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $t) {
          $t->id();
          $t->foreignId('order_id')->constrained()->cascadeOnDelete();
          $t->foreignId('nft_id')->constrained()->restrictOnDelete();
          $t->unsignedSmallInteger('quantity');
          $t->decimal('unit_price_crypto', 18, 8);
          $t->decimal('unit_price_gbp', 10, 2);
          $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
