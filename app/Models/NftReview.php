<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NftReview extends Model
{
    protected $fillable = [
        'nft_id',
        'user_id',
        'rating',
        'review_text'
    ];

    public function nft()
    {
        return $this->belongsTo(Nft::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
