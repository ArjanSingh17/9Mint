<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $t) {
            $t->foreignId('submitted_by_user_id')->nullable()->after('creator_name')->constrained('users')->nullOnDelete();
            $t->string('approval_status', 20)->default('approved')->after('submitted_by_user_id');
            $t->timestamp('approved_at')->nullable()->after('approval_status');
            $t->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $t->timestamp('rejected_at')->nullable()->after('approved_by');
            $t->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $t->text('rejection_reason')->nullable()->after('rejected_by');

            $t->string('creation_fee_payment_state', 32)->default('not_required')->after('rejection_reason');
            $t->string('creation_fee_refund_state', 32)->default('none')->after('creation_fee_payment_state');
            $t->foreignId('creation_fee_order_id')->nullable()->after('creation_fee_refund_state')->constrained('orders')->nullOnDelete();
            $t->string('creation_fee_payment_intent_id', 64)->nullable()->after('creation_fee_order_id');
            $t->string('creation_fee_provider', 32)->nullable()->after('creation_fee_payment_intent_id');
            $t->decimal('creation_fee_amount_gbp', 10, 2)->nullable()->after('creation_fee_provider');
            $t->string('creation_fee_hold_currency', 10)->nullable()->after('creation_fee_amount_gbp');
            $t->decimal('creation_fee_hold_amount', 36, 18)->nullable()->after('creation_fee_hold_currency');
            $t->string('creation_fee_hold_reference', 64)->nullable()->after('creation_fee_hold_amount');

            $t->index('approval_status');
            $t->index('creation_fee_payment_state');
            $t->unique('creation_fee_hold_reference');
        });

        Schema::table('nfts', function (Blueprint $t) {
            $t->foreignId('submitted_by_user_id')->nullable()->after('collection_id')->constrained('users')->nullOnDelete();
            $t->string('approval_status', 20)->default('approved')->after('submitted_by_user_id');
            $t->timestamp('approved_at')->nullable()->after('approval_status');
            $t->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $t->timestamp('rejected_at')->nullable()->after('approved_by');
            $t->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $t->text('rejection_reason')->nullable()->after('rejected_by');

            $t->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('nfts', function (Blueprint $t) {
            $t->dropForeign(['submitted_by_user_id']);
            $t->dropForeign(['approved_by']);
            $t->dropForeign(['rejected_by']);
            $t->dropIndex(['approval_status']);
            $t->dropColumn([
                'submitted_by_user_id',
                'approval_status',
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'rejection_reason',
            ]);
        });

        Schema::table('collections', function (Blueprint $t) {
            $t->dropForeign(['submitted_by_user_id']);
            $t->dropForeign(['approved_by']);
            $t->dropForeign(['rejected_by']);
            $t->dropForeign(['creation_fee_order_id']);
            $t->dropIndex(['approval_status']);
            $t->dropIndex(['creation_fee_payment_state']);
            $t->dropUnique(['creation_fee_hold_reference']);
            $t->dropColumn([
                'submitted_by_user_id',
                'approval_status',
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'rejection_reason',
                'creation_fee_payment_state',
                'creation_fee_refund_state',
                'creation_fee_order_id',
                'creation_fee_payment_intent_id',
                'creation_fee_provider',
                'creation_fee_amount_gbp',
                'creation_fee_hold_currency',
                'creation_fee_hold_amount',
                'creation_fee_hold_reference',
            ]);
        });
    }
};
