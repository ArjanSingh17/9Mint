<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fx_rate_snapshots', function (Blueprint $t) {
            $t->id();
            $t->string('base_currency', 10);
            $t->json('rates_json');
            $t->string('provider', 32);
            $t->timestamp('rated_at');
            $t->timestamps();

            $t->index(['base_currency', 'rated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_rate_snapshots');
    }
};
