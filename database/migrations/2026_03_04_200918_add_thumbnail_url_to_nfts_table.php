<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nfts', function (Blueprint $t) {
            $t->string('thumbnail_url')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('nfts', function (Blueprint $t) {
            $t->dropColumn('thumbnail_url');
        });
    }
};
