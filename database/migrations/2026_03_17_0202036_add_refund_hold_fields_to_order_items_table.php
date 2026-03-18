<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('lifecycle_status', 32)
                ->default('checkout_pending')
                ->after('pay_currency');
            $table->timestamp('hold_expires_at')->nullable()->after('lifecycle_status');
            $table->timestamp('hold_extended_until')->nullable()->after('hold_expires_at');
            $table->timestamp('refund_requested_at')->nullable()->after('hold_extended_until');
            $table->timestamp('refund_decided_at')->nullable()->after('refund_requested_at');
            $table->timestamp('investigation_requested_at')->nullable()->after('refund_decided_at');
            $table->timestamp('finalized_at')->nullable()->after('investigation_requested_at');

            $table->string('refund_reason', 120)->nullable()->after('finalized_at');
            $table->text('refund_notes')->nullable()->after('refund_reason');
            $table->text('refund_denial_reason')->nullable()->after('refund_notes');
            $table->foreignId('refund_decided_by_user_id')
                ->nullable()
                ->after('refund_denial_reason')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['lifecycle_status']);
            $table->index(['hold_expires_at']);
            $table->index(['hold_extended_until']);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['refund_decided_by_user_id']);
            $table->dropIndex(['lifecycle_status']);
            $table->dropIndex(['hold_expires_at']);
            $table->dropIndex(['hold_extended_until']);

            $table->dropColumn([
                'lifecycle_status',
                'hold_expires_at',
                'hold_extended_until',
                'refund_requested_at',
                'refund_decided_at',
                'investigation_requested_at',
                'finalized_at',
                'refund_reason',
                'refund_notes',
                'refund_denial_reason',
                'refund_decided_by_user_id',
            ]);
        });
    }
};
