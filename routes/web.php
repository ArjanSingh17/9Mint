<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\ProfileController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    // Show the user's profile information
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Handle the form submission to update the profile
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});