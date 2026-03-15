<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserProfilePolicy
{

public function view(User $user, User $targetUser): bool
{
    // viewing their own profile
    if ($user->id === $targetUser->id) {
        return true; 
    }

    // Admin Management (Admin with explicit permission)
    if ($user->hasRole('admin') && $user->can('manage_users')) {
        return true;
    }

    // DENY BY DEFAULT
    return false;
}

public function update(User $user, User $targetUser): bool
{
    // The same security rules apply to updating data as viewing it.
    if ($user->id === $targetUser->id) {
        return true; 
    }

    if ($user->hasRole('admin') && $user->can('manage_users')) {
        return true;
    }

    return false;
}
}
