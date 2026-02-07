<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NftToken extends Model
{
    protected $fillable = [
        'nft_id',
        'serial_number',
        'owner_user_id',
        'first_sale_order_id',
        'status',
    ];

    public function nft() { return $this->belongsTo(Nft::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_user_id'); }
    public function listing() { return $this->hasOne(Listing::class, 'token_id'); }
}
