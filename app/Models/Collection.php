<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'slug','name','description','cover_image_url','creator_name',
        'submitted_by_user_id',
        'approval_status',
        'is_public',
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
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'creation_fee_amount_gbp' => 'decimal:2',
        'creation_fee_hold_amount' => 'decimal:18',
    ];

    public function scopeApproved($query)
    {
        return $query
            ->where('approval_status', self::APPROVAL_APPROVED)
            ->where('is_public', true);
    }

    public function nfts() { return $this->hasMany(Nft::class); }

    public function uploadFolderName(): string
    {
        return "{$this->slug}-{$this->id}";
    }
}
