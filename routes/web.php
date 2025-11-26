<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes (Session-based, for traditional web pages)
|--------------------------------------------------------------------------
*/

// If you need a web-based login page in the future, create a separate WebAuthController
// For now, redirect to API documentation or your React app
Route::get('/login', function () {
    return response()->json([
        'message' => 'This is an API-only application. Please use POST /api/v1/login',
        'documentation' => url('/api/documentation')
    ], 200);
});

Route::get('/', function () {
    return response()->json([
        'app' => '9Mint NFT Marketplace',
        'version' => '1.0',
        'api_base' => url('/api/v1'),
    ]);
});

// Profile routes (if you actually need web-based profile pages)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'showSelf'])->name('profile.show');
    Route::patch('/profile', [UserProfileController::class, 'updateSelf'])->name('profile.update');
    Route::patch('/profile/password', [UserProfileController::class, 'updatePassword'])->name('password.update');
});

// Admin web routes (if needed)
Route::group([
    'middleware' => ['auth:sanctum', 'role:admin'],
    'prefix' => 'admin'
], function () {
    Route::get('/users/{user}', [UserProfileController::class, 'showUser'])->name('admin.users.show');
    Route::patch('/users/{user}', [UserProfileController::class, 'updateUser'])->name('admin.users.update');
});