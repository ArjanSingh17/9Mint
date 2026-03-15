<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nft_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('nft_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('serial_number');
            $t->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('first_sale_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $t->string('status', 16)->default('owned');
            $t->timestamps();

            $t->unique(['nft_id', 'serial_number']);
            $t->index(['status', 'owner_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nft_tokens');
    }
};
