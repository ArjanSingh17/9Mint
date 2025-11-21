<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\ProfileController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    // view and update details
    Route::get('/profile', [UserProfileController::class, 'showSelf'])->name('profile.show');
    // Handle the form submission to update the profile
    Route::patch('/profile', [UserProfileController::class, 'updateSelf'])->name('profile.update');

    // change Password
    Route::patch('/profile/password', [UserProfileController::class, 'updatePassword'])->name('password.update');
});


// --- Define the routes that allow an Admin to manage any user's profile

Route::group([
    'middleware' => ['auth', 'role:admin'], // Must be logged in AND have the 'admin' role
    'prefix' => 'admin' 
], function () {
    // GET /admin/users/{user} -> Admin views a specific customer's profile
    Route::get('/users/{user}', [UserProfileController::class, 'showUser'])->name('admin.users.show');
    
    // PATCH /admin/users/{user} -> Admin updates a specific customer's profile
    Route::patch('/users/{user}', [UserProfileController::class, 'updateUser'])->name('admin.users.update');
});