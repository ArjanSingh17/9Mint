<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';

    protected $fillable = [
        'requester_id',
        'addressee_id',
        'user_low_id',
        'user_high_id',
        'status',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    public function scopeBetweenUsers($query, int $userA, int $userB)
    {
        [$lowId, $highId] = self::normalizedPair($userA, $userB);

        return $query
            ->where('user_low_id', $lowId)
            ->where('user_high_id', $highId);
    }

    public static function normalizedPair(int $userA, int $userB): array
    {
        return $userA < $userB ? [$userA, $userB] : [$userB, $userA];
    }

    public static function between(int $userA, int $userB): ?self
    {
        return self::query()->betweenUsers($userA, $userB)->first();
    }

    public static function stateForViewer(int $viewerId, int $otherUserId): string
    {
        if ($viewerId === $otherUserId) {
            return 'self';
        }

        $friendship = self::between($viewerId, $otherUserId);

        if (! $friendship) {
            return 'none';
        }

        if ($friendship->status === self::STATUS_ACCEPTED) {
            return 'friends';
        }

        return (int) $friendship->requester_id === $viewerId
            ? 'outgoing_pending'
            : 'incoming_pending';
    }

    public static function areFriends(int $userA, int $userB): bool
    {
        if ($userA === $userB) {
            return false;
        }

        return self::query()
            ->betweenUsers($userA, $userB)
            ->where('status', self::STATUS_ACCEPTED)
            ->exists();
    }
}
