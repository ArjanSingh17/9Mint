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
        Schema::create('nfts', function (Blueprint $t) {
          $t->id();
          $t->foreignId('collection_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
          $t->string('slug',160)->unique();
          $t->string('name',160);
          $t->text('description')->nullable();
          $t->string('image_url');
          $t->string('currency_code',10)->default('ETH');
          $t->decimal('price_crypto', 18, 8);
          $t->unsignedInteger('editions_total');
          $t->unsignedInteger('editions_remaining');
          $t->boolean('is_active')->default(true);
          $t->softDeletes(); // optional
          $t->timestamps();
          $t->index(['collection_id','is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfts');
    }
};
