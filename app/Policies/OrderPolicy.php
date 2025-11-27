<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{

/**
 * Determine whether the user can view the model.
 */
public function view(User $user, Order $order): bool
{
    // 1. ALLOWED: Check for Self-Ownership
    // The user ID must match the user_id stored on the order record.
    if ($user->id === $order->user_id) {
        return true;
    }

    // 2. ALLOWED: Check for Admin Override
    // Admin needs to be able to view all orders, so they must have the permission.
    if ($user->hasRole('admin') && $user->can('view_all_orders')) {
        return true;
    }

    // DENY BY DEFAULT: If neither condition is met, access is denied (returns false).
    return false;
}

public function process(User $user, Order $order): bool
{
    // This permission should only be granted to authorized Admins.
    if ($user->hasRole('admin') && $user->can('process_order')) {
        return true;
    }
    
    // DENY BY DEFAULT
    return false;
}

public function viewAny(User $user): bool
{
    // 1. ALLOWED: Check if the user is a standard customer.
    // All logged-in customers must be able to view their own order history list.
    if ($user->hasRole('customer')) {
        return true;
    }

    // 2. ALLOWED: Check for Admin Read Access.
    // Admins must be able to see the full list of all orders for management.
    // We check the specific 'view_all_orders' permission.
    if ($user->hasRole('admin') && $user->can('view_all_orders')) {
        return true;
    }

    // DENY BY DEFAULT: If they are neither a customer nor an authorized admin.
    return false;

}
