<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'collections';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'cover_image_url',
        'creator_name',
    ];

    public function nfts()
    {
        return $this->hasMany(Nft::class);
    }
}
