<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/login', [AuthController::class, 'loginWeb']);
    Route::post('/register', [AuthController::class, 'registerWeb']);
});

Route::get('/', function () {
    return view('login-register');
});

Route::get('/checkout', function () {
    return view('checkout');
});

Route::get('/homepage', function () {
    return view('Homepage');
});

Route::get('/products', function () {
    return view('products');
});

Route::get('/products/Glossy-collection', function () {
    return view('Glossy-collection');
});

Route::get('/pricing', function () {
    return view('Pricing');
});

Route::get('/products/SuperheroCollection', function () {
    return view('SuperheroCollection');
});

Route::get('/contactUs', function () {
    return view('ContactUs');
});

Route::get('/aboutUs', function () {
    return view('aboutUs');
});

Route::get('/contactUs/terms', function () {
    return view('TermsAndConditions');
});

Route::get('/contactUs/faqs', function () {
    return view('Faqs');
});

// AUTHENTICATION

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');

    Route::post('/cart', function (Request $r) {
            // TODO: implement cart here
            return back()->with('status', 'Added to basket (stub)');
        })->name('cart.store');

    // view and update details
  //  Route::get('/profile', [UserProfileController::class, 'showSelf'])->name('profile.show');
    // Handle the form submission to update the profile
    //Route::patch('/profile', [UserProfileController::class, 'updateSelf'])->name('profile.update');

    // change Password
    //Route::patch('/profile/password', [UserProfileController::class, 'updatePassword'])->name('password.update');
});


// --- Define the routes that allow an Admin to manage any user's profile

//Route::group([
  //  'middleware' => ['auth', 'role:admin'], // Must be logged in AND have the 'admin' role
    //'prefix' => 'admin'
//], function () {
    // GET /admin/users/{user} -> Admin views a specific customer's profile
  //  Route::get('/users/{user}', [UserProfileController::class, 'showUser'])->name('admin.users.show');

    // PATCH /admin/users/{user} -> Admin updates a specific customer's profile
    //Route::patch('/users/{user}', [UserProfileController::class, 'updateUser'])->name('admin.users.update');
//});
