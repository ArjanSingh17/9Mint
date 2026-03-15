<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesHistory extends Model
{
    protected $fillable = [
        'listing_id',
        'token_id',
        'order_id',
        'pay_amount',
        'pay_currency',
        'sold_at',
    ];

    protected $casts = [
        'pay_amount' => 'decimal:18',
        'sold_at' => 'datetime',
    ];

    public function listing() { return $this->belongsTo(Listing::class); }
    public function token() { return $this->belongsTo(NftToken::class, 'token_id'); }
    public function order() { return $this->belongsTo(Order::class); }
}
