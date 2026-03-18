<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public const LIFECYCLE_CHECKOUT_PENDING = 'checkout_pending';
    public const LIFECYCLE_HOLD_PENDING = 'hold_pending';
    public const LIFECYCLE_REFUND_REQUESTED = 'refund_requested';
    public const LIFECYCLE_REFUND_APPROVED = 'refund_approved';
    public const LIFECYCLE_REFUND_DENIED = 'refund_denied';
    public const LIFECYCLE_INVESTIGATION_REQUESTED = 'investigation_requested';
    public const LIFECYCLE_FINALIZED = 'finalized';

    protected $fillable = [
        'order_id',
        'listing_id',
        'token_id',
        'quantity',
        'ref_unit_amount',
        'ref_currency',
        'pay_unit_amount',
        'pay_currency',
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
    ];

    protected $casts = [
        'ref_unit_amount' => 'decimal:18',
        'pay_unit_amount' => 'decimal:18',
        'hold_expires_at' => 'datetime',
        'hold_extended_until' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refund_decided_at' => 'datetime',
        'investigation_requested_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function listing() { return $this->belongsTo(Listing::class); }
    public function token()   { return $this->belongsTo(NftToken::class, 'token_id'); }
    public function refundDecidedBy() { return $this->belongsTo(User::class, 'refund_decided_by_user_id'); }

    public function holdReleaseAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->hold_extended_until) {
            return $this->hold_extended_until;
        }

        return $this->hold_expires_at;
    }

    public function isHeld(): bool
    {
        return in_array($this->lifecycle_status, [
            self::LIFECYCLE_HOLD_PENDING,
            self::LIFECYCLE_REFUND_REQUESTED,
            self::LIFECYCLE_REFUND_DENIED,
            self::LIFECYCLE_INVESTIGATION_REQUESTED,
        ], true) && optional($this->holdReleaseAt())->isFuture();
    }
}
