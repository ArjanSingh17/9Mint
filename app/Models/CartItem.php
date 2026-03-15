<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id','listing_id','nft_id','quantity','selected_pay_currency'];
    public function user() { return $this->belongsTo(User::class); }
    public function listing() { return $this->belongsTo(Listing::class); }
    public function nft() { return $this->belongsTo(Nft::class); }
}
