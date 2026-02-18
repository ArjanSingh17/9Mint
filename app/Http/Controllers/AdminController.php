<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        // Fetch all users from the database
        $users = User::all();

        return view('admin.users', compact('users'));
    }

    public function deleteUser($id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);

        // Prevent deleting yourself 
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself!');
        }

        // Delete the user
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    // Show the form with current user data
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users_edit', compact('user'));
    }

    // Process the update
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validate the inputs
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id, // Allow their CURRENT email
            'role' => 'required|in:admin,customer', // Only allow these specific roles
        ]);

        // Update the user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

public function inventory()
{
    // Fetch all NFTs and their collection info
    $nfts = \App\Models\Nft::with('collection')->get();

    return view('admin.inventory', compact('nfts'));
}
}
