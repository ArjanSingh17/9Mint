<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasApiTokens;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'banned_at',
        'banned_by',
        'badges',
        'profile_image_url',
        'description',
        'wallet_address',
        'nfts_public',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'nfts_public' => 'boolean',
            'banned_at' => 'datetime',
            'badges' => 'array',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === '9Mint';
    }

    public function isBanned(): bool
    {
        return ! is_null($this->banned_at);
    }

    /**
     * @return array<int, array{key:string,label:string,description:string}>
     */
    public function profileBadges(): array
    {
        $result = [];

        $pushBadge = function (string $key, string $label, string $description) use (&$result): void {
            if (isset($result[$key])) {
                return;
            }

            $result[$key] = [
                'key' => $key,
                'label' => $label,
                'description' => $description,
            ];
        };

        if ($this->isSuperAdmin()) {
            $pushBadge('superadmin', 'Superadmin', 'Full platform control, including assigning other admins.');
        } elseif (strtolower((string) $this->role) === 'admin') {
            $pushBadge('admin', 'Admin', 'Can moderate submissions and manage platform operations.');
        }

        if ($this->isBanned()) {
            $pushBadge('banned', 'Banned', 'Account is restricted from trading, purchases, and wallet actions.');
        }

        foreach ((array) ($this->badges ?? []) as $index => $badge) {
            if (is_string($badge)) {
                $label = trim($badge);
                if ($label === '') {
                    continue;
                }

                $key = 'custom_' . str($label)->slug('_')->toString();
                $pushBadge($key, $label, 'Community badge.');
                continue;
            }

            if (is_array($badge)) {
                $label = trim((string) ($badge['label'] ?? $badge['name'] ?? ''));
                if ($label === '') {
                    continue;
                }

                $key = trim((string) ($badge['key'] ?? ''));
                if ($key === '') {
                    $key = 'custom_' . str($label)->slug('_')->toString() . '_' . $index;
                }

                $description = trim((string) ($badge['description'] ?? 'Community badge.'));
                $pushBadge($key, $label, $description === '' ? 'Community badge.' : $description);
            }
        }

        return array_values($result);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function favourites(): BelongsToMany
    {
        return $this->belongsToMany(Nft::class, 'favourites');

    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function conversations(): HasMany
{
    return $this->hasMany(Conversation::class, 'sender_id')
        ->where(function($query) {
            $query->where('sender_id', $this->id)
                  ->orWhere('receiver_id', $this->id);
        });
}

    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.' . $this->id;
    }

           public function getOtherUsers()
{
    return User::where('id', '!=', auth()->id())
        ->select('id', 'name', 'email') 
        ->get();
}
}
