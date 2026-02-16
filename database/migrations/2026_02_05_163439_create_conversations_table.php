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
        Schema::create('conversations', function (Blueprint $table) {
           $table->id();

    $table->foreignId('ticket_id')
        ->constrained('tickets')
        ->cascadeOnDelete();

    $table->foreignId('sender_id')
        ->constrained('users')
        ->cascadeOnDelete();

    $table->softDeletes();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
