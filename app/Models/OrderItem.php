<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id','nft_id','quantity','unit_price_crypto','unit_price_gbp'
    ];

    protected $casts = [
        'unit_price_crypto' => 'decimal:8',
        'unit_price_gbp'    => 'decimal:2',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function nft()   { return $this->belongsTo(Nft::class); }
}
