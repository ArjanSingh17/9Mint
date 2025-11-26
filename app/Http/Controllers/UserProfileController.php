<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
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
   $validatedData = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        // checks email uniqueness but IGNORING the current user's ID
        'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        // Wallet address is nullable but must be a string if provided.
        'wallet_address' => ['nullable', 'string', 'max:100'], 
    ]);

    // Fill the model with validated data
    $user->fill($validatedData);

    // Save to the database
    $user->save();
    
    // Give success feedback and redirect back to the profile page
    return redirect()->route('profile.show')->with('status', 'Profile details updated successfully!');
}




public function updatePassword(Request $request)
{
    $user = $request->user(); 

    // 'current_password' checks against the hashed password in the DB
    // 'confirmed' checks that the 'password' field matches the 'password_confirmation' field
    $request->validate([
        'current_password' => ['required', 'string', 'current_password'], 
        'password' => ['required', 'confirmed', 'min:8'], 
    ]);

    //  Hash and update the password
    $user->password = Hash::make($request->password);
    $user->save();

    // Give success feedback
    return redirect()->route('profile.show')->with('status', 'Password updated successfully!');
}


// methods that handle the Admin's requests
public function showUser(User $targetUser)
{
    // This executes the logic from UserProfilePolicy->view()
    $this->authorize('view', $targetUser); 

    // If authorisation passes, the Admin can safely view the data
    return view('admin.users.admin_user_manage', compact('targetUser'));
}


 // Update a specific customer's profile (for Admin)
public function updateUser(Request $request, User $targetUser)
{
    // Checks UserProfilePolicy->update() before allowing any changes.
    $this->authorize('update', $targetUser); 

    $validatedData = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($targetUser->id)],
        'wallet_address' => ['nullable', 'string', 'max:100'], 
    ]);
    
    // Update the target user model with validated data
    $targetUser->fill($validatedData);

    // Save to the database
    $targetUser->save();
    
    // Give feedback
    return redirect()->route('admin.users.show', $targetUser)->with('status', 'Customer data updated successfully.');
}

}
