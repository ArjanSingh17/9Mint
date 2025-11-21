<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function showSelf()
{
    // 'auth' middleware guarantees a logged-in user.
    $user = Auth::user(); 
    
    return view('profile.customer_profile', compact('user'));
}

public function updateSelf(Request $request)
{
    $user = $request->user();
    
    // Validate and sanitize input before saving
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        // Add wallet address validation here
    ]);

    // Update $user fields and save    
    
    return redirect()->route('profile.show')->with('status', 'Profile updated successfully.');
}

//---
//update password here...
//----


// methods that handle the Admin's requests

public function showUser(User $targetUser)
{
    // This executes the logic from UserProfilePolicy->view()
    $this->authorize('view', $targetUser); 

    // If authorisation passes, the Admin can safely view the data
    return view('admin.users.admin_user_manage', compact('targetUser'));
}

/**
 * Update a specific customer's profile (for Admin).
 */
public function updateUser(Request $request, User $targetUser)
{
    // Checks UserProfilePolicy->update() before allowing any changes.
    $this->authorize('update', $targetUser); 

    // 1. Validation and data handling goes here (similar to updateSelf, but for Admin roles)
    // $request->validate([...]);
    
    // Update $targetUser data and save...
    
    return redirect()->route('admin.users.show', $targetUser)->with('status', 'Customer data updated successfully.');
}

}
