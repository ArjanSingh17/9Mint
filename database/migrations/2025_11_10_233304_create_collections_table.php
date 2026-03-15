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
        Schema::create('collections', function (Blueprint $t) {
          $t->id();
          $t->string('slug',120)->unique();
          $t->string('name',120);
          $t->text('description')->nullable();
          $t->string('cover_image_url')->nullable();
          $t->string('creator_name',120)->nullable();
          $t->softDeletes(); // optional
          $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
