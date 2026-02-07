<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'listing_id',
        'token_id',
        'quantity',
        'ref_unit_amount',
        'ref_currency',
        'pay_unit_amount',
        'pay_currency',
    ];

    protected $casts = [
        'ref_unit_amount' => 'decimal:18',
        'pay_unit_amount' => 'decimal:18',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function listing() { return $this->belongsTo(Listing::class); }
    public function token()   { return $this->belongsTo(NftToken::class, 'token_id'); }
}
