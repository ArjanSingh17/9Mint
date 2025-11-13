<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'slug','name','description','cover_image_url','creator_name',
    ];

    public function nfts() { return $this->hasMany(Nft::class); }
}
