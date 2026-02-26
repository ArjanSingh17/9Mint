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
    Schema::table('nft_reviews', function (Blueprint $table) {
        if (Schema::hasColumn('nft_reviews', 'name')) {
            $table->dropColumn('name');
        }

        if (Schema::hasColumn('nft_reviews', 'review')) {
            $table->dropColumn('review');
        }
    });
}


    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('nft_reviews', function (Blueprint $table) {
        $table->string('name')->nullable();
        $table->text('review')->nullable();
    });
}
};