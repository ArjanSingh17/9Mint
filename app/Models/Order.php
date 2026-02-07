<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'pay_currency',
        'pay_total_amount',
        'ref_currency',
        'ref_total_amount',
        'fx_rate_snapshot_id',
        'fx_provider',
        'fx_rate',
        'fx_rated_at',
        'expires_at',
        'placed_at',
        'checkout_token',
    ];

    protected $casts = [
        'pay_total_amount' => 'decimal:18',
        'ref_total_amount' => 'decimal:18',
        'fx_rate'          => 'array',
        'fx_rated_at'      => 'datetime',
        'expires_at'       => 'datetime',
        'placed_at'        => 'datetime',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function items()      { return $this->hasMany(OrderItem::class); }
    public function fxSnapshot() { return $this->belongsTo(FxRateSnapshot::class, 'fx_rate_snapshot_id'); }
}
