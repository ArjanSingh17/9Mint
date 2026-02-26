<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Nft extends Model
{
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'slug','name','description','image_url',
        'primary_ref_amount','primary_ref_currency',
        'editions_total','editions_remaining',
        'is_active','collection_id',
        'submitted_by_user_id',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'primary_ref_amount' => 'decimal:18',
    ];

    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    public function scopeMarketVisible($query)
    {
        return $query
            ->where('is_active', true)
            ->approved()
            ->whereHas('collection', function ($q) {
                $q->approved();
            });
    }

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
