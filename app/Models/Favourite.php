<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    protected $table = 'favourites';
    protected $fillable = ['user_id','nft_id'];

    public function user() { return $this->belongsTo(User::class); }
    public function nft()  { return $this->belongsTo(Nft::class); }
}
