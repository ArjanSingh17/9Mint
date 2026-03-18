<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'search_public')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $column = $table->boolean('search_public')->default(true);

            if (Schema::hasColumn('users', 'nfts_public')) {
                $column->after('nfts_public');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'search_public')) {
                $table->dropColumn('search_public');
            }
        });
    }
};
