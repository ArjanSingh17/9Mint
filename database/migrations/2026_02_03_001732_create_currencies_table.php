<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $t) {
            $t->string('code', 10)->primary();
            $t->string('type', 10);
            $t->unsignedTinyInteger('decimals')->default(2);
            $t->string('symbol', 8)->nullable();
            $t->boolean('is_enabled')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
