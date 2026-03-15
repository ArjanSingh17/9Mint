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
        Schema::create('cart_items', function (Blueprint $t) {
          $t->id();
          $t->foreignId('user_id')->constrained()->cascadeOnDelete();
          $t->foreignId('nft_id')->constrained()->restrictOnDelete();
          $t->unsignedSmallInteger('quantity')->default(1);
          $t->timestamps();
          $t->unique(['user_id','nft_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
