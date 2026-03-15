<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('provider', 32);
            $t->string('status', 16)->default('created');
            $t->json('metadata')->nullable();
            $t->timestamps();

            $t->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};
