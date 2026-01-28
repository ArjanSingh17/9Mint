<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Nft extends Model
{
    protected $fillable = [
        'slug','name','description','image_url','currency_code',
        'price_crypto','price_small_gbp','price_medium_gbp','price_large_gbp',
        'editions_total','editions_remaining',
        'is_active','collection_id',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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
