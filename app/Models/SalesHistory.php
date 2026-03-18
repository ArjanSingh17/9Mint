<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesHistory extends Model
{
    public const SETTLEMENT_PENDING = 'pending';
    public const SETTLEMENT_RELEASED = 'released';
    public const SETTLEMENT_CANCELLED = 'cancelled';

    protected $fillable = [
        'listing_id',
        'token_id',
        'order_id',
        'pay_amount',
        'pay_currency',
        'sold_at',
        'settlement_status',
        'settlement_eligible_at',
        'settlement_released_at',
        'settlement_cancelled_at',
        'settlement_metadata',
    ];

    protected $casts = [
        'pay_amount' => 'decimal:18',
        'sold_at' => 'datetime',
        'settlement_eligible_at' => 'datetime',
        'settlement_released_at' => 'datetime',
        'settlement_cancelled_at' => 'datetime',
        'settlement_metadata' => 'array',
    ];

    public function listing() { return $this->belongsTo(Listing::class); }
    public function token() { return $this->belongsTo(NftToken::class, 'token_id'); }
    public function order() { return $this->belongsTo(Order::class); }
}
