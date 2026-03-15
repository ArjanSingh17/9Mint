<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('token_id')->constrained('nft_tokens')->cascadeOnDelete();
            $t->foreignId('seller_user_id')->constrained('users')->cascadeOnDelete();
            $t->string('status', 16)->default('active');
            $t->decimal('ref_amount', 36, 18);
            $t->string('ref_currency', 10);
            $t->timestamp('reserved_until')->nullable();
            $t->foreignId('reserved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['status', 'reserved_until']);
            $t->index(['seller_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
