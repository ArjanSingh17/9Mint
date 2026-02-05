<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'type',
        'decimals',
        'symbol',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'decimals' => 'integer',
    ];
}
