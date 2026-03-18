<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Collection;

class UserNotificationService
{
    public function notifyUser(int $userId, string $type, string $title, ?string $body = null, array $data = []): UserNotification
    {
        return UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data ?: null,
        ]);
    }

    public function notifyAdmins(string $type, string $title, ?string $body = null, array $data = []): void
    {
        $adminIds = User::query()
            ->where('role', 'admin')
            ->pluck('id');

        foreach ($adminIds as $adminId) {
            $this->notifyUser((int) $adminId, $type, $title, $body, $data);
        }
    }

    public function unreadCountForUser(int $userId): int
    {
        return UserNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function recentForUser(int $userId, int $limit = 10): Collection
    {
        return UserNotification::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
