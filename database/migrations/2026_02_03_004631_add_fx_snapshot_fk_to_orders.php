<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'fx_rate_snapshot_id')) {
            Schema::table('orders', function (Blueprint $t) {
                $t->unsignedBigInteger('fx_rate_snapshot_id')->nullable()->after('id');
            });
        }

        Schema::table('orders', function (Blueprint $t) {
            $t->foreign('fx_rate_snapshot_id')
                ->references('id')
                ->on('fx_rate_snapshots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'fx_rate_snapshot_id')) {
            Schema::table('orders', function (Blueprint $t) {
                $t->dropForeign(['fx_rate_snapshot_id']);
            });
        }
    }
};
