<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('currency', 10);
            $t->enum('type', ['credit', 'debit']);
            $t->decimal('amount', 36, 18);
            $t->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $t->foreignId('listing_id')->nullable()->constrained('listings')->nullOnDelete();
            $t->string('fx_provider', 32)->nullable();
            $t->json('fx_rate')->nullable();
            $t->timestamp('fx_rated_at')->nullable();
            $t->json('metadata')->nullable();
            $t->timestamps();

            $t->index(['user_id', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
