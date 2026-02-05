<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'user_id',
        'currency',
        'type',
        'amount',
        'order_id',
        'listing_id',
        'fx_provider',
        'fx_rate',
        'fx_rated_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:18',
        'fx_rate' => 'array',
        'fx_rated_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
