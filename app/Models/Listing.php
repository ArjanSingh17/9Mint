<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    public const PLATFORM_FEE_RATE = 0.015;
    public const CREATOR_FEE_RATE = 0.010;
    public const SERVICE_FEE_RATE = 0.025;

    protected $fillable = [
        'token_id',
        'seller_user_id',
        'status',
        'ref_amount',
        'ref_currency',
        'reserved_until',
        'reserved_by_user_id',
    ];

    protected $casts = [
        'ref_amount' => 'decimal:18',
        'reserved_until' => 'datetime',
    ];

    public function token() { return $this->belongsTo(NftToken::class, 'token_id'); }
    public function seller() { return $this->belongsTo(User::class, 'seller_user_id'); }
    public function reservedBy() { return $this->belongsTo(User::class, 'reserved_by_user_id'); }

    public static function sellerNetAmount(float $gross): float
    {
        return $gross * (1 - self::SERVICE_FEE_RATE);
    }
}
