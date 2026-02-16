<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Nft extends Model
{
    protected $fillable = [
        'slug','name','description','image_url',
        'editions_total','editions_remaining',
        'is_active','collection_id',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, NftToken::class, 'nft_id', 'token_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(NftToken::class);
    }

    public function listings(): HasMany
    {
        return $this->hasManyThrough(Listing::class, NftToken::class, 'nft_id', 'token_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function favouritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favourites');
    }
}
