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
        Schema::create('favourites', function (Blueprint $t) {
          $t->foreignId('user_id')->constrained()->cascadeOnDelete();
          $t->foreignId('nft_id')->constrained()->cascadeOnDelete();
          $t->timestamps();
          $t->unique(['user_id','nft_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favourites');
    }
};
