<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function nfts(): HasMany
    {
        return $this->hasMany(Nft::class);
    }
}
