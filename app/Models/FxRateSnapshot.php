<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FxRateSnapshot extends Model
{
    protected $fillable = [
        'base_currency',
        'rates_json',
        'provider',
        'rated_at',
    ];

    protected $casts = [
        'rates_json' => 'array',
        'rated_at' => 'datetime',
    ];
}
