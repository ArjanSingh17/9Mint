<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

           
            $table->enum('type', ['ticket', 'user'])
                ->default('ticket');

            $table->foreignId('ticket_id')
                ->nullable()
                ->constrained('tickets')
                ->cascadeOnDelete();

            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

           
            $table->foreignId('receiver_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};