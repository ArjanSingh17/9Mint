<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nft extends Model
{
    protected $fillable = [
        'slug','name','description','image_url','currency_code',
        'price_crypto','editions_total','editions_remaining',
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
