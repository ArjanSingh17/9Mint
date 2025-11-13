<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id','status','currency_code','total_crypto','total_gbp','placed_at'
    ];

    protected $casts = [
        'total_crypto' => 'decimal:8',
        'total_gbp'    => 'decimal:2',
        'placed_at'    => 'datetime',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function items()      { return $this->hasMany(OrderItem::class); }
}
