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
        Schema::create('orders', function (Blueprint $t) {
          $t->id();
          $t->foreignId('user_id')->constrained()->restrictOnDelete();
          $t->string('status',20)->default('pending'); // or tinyint
          $t->string('currency_code',10)->default('ETH');
          $t->decimal('total_crypto', 18, 8);
          $t->decimal('total_gbp', 10, 2);
          $t->timestamp('placed_at')->nullable();
          $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
