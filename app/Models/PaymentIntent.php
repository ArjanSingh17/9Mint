<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentIntent extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'order_id',
        'provider',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($intent) {
            if (empty($intent->id)) {
                $intent->id = (string) Str::uuid();
            }
        });
    }

    public function order() { return $this->belongsTo(Order::class); }
}
