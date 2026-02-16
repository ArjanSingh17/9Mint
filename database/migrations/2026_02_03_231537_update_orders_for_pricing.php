<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            if (!Schema::hasColumn('orders', 'fx_rate_snapshot_id')) {
                $t->unsignedBigInteger('fx_rate_snapshot_id')->nullable()->after('id');
            }
            $t->string('pay_currency', 10)->nullable()->after('status');
            $t->decimal('pay_total_amount', 36, 18)->nullable()->after('pay_currency');
            $t->string('ref_currency', 10)->nullable()->after('pay_total_amount');
            $t->decimal('ref_total_amount', 36, 18)->nullable()->after('ref_currency');
            $t->string('fx_provider', 32)->nullable()->after('ref_total_amount');
            $t->json('fx_rate')->nullable()->after('fx_provider');
            $t->timestamp('fx_rated_at')->nullable()->after('fx_rate');
            $t->timestamp('expires_at')->nullable()->after('fx_rated_at');
        });

        Schema::table('orders', function (Blueprint $t) {
            $t->dropColumn(['currency_code', 'total_crypto', 'total_gbp']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->string('currency_code', 10)->default('ETH')->after('status');
            $t->decimal('total_crypto', 18, 8)->default(0)->after('currency_code');
            $t->decimal('total_gbp', 10, 2)->default(0)->after('total_crypto');
        });

        Schema::table('orders', function (Blueprint $t) {
            $t->dropColumn([
                'fx_rate_snapshot_id',
                'pay_currency',
                'pay_total_amount',
                'ref_currency',
                'ref_total_amount',
                'fx_provider',
                'fx_rate',
                'fx_rated_at',
                'expires_at',
            ]);
        });
    }
};
