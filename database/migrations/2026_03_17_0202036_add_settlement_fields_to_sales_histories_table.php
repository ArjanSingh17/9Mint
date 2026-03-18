<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_histories', function (Blueprint $table) {
            $table->string('settlement_status', 32)
                ->default('pending')
                ->after('sold_at');
            $table->timestamp('settlement_eligible_at')->nullable()->after('settlement_status');
            $table->timestamp('settlement_released_at')->nullable()->after('settlement_eligible_at');
            $table->timestamp('settlement_cancelled_at')->nullable()->after('settlement_released_at');
            $table->json('settlement_metadata')->nullable()->after('settlement_cancelled_at');

            $table->index(['settlement_status', 'settlement_eligible_at'], 'sales_histories_settlement_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sales_histories', function (Blueprint $table) {
            $table->dropIndex('sales_histories_settlement_idx');
            $table->dropColumn([
                'settlement_status',
                'settlement_eligible_at',
                'settlement_released_at',
                'settlement_cancelled_at',
                'settlement_metadata',
            ]);
        });
    }
};
