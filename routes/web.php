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

Route::get('/cart', function () {
    return view('cart');
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
            // Get or create cart in session
            $cart = session()->get('cart', []);

            // Get the NFT slug and size from the request
            $nftSlug = $r->input('nft_slug');
            $size = $r->input('size');

            // Create a unique key for this item (slug + size)
            $itemKey = $nftSlug . '_' . $size;

            // If item already exists in cart, increment quantity
            if (isset($cart[$itemKey])) {
                $cart[$itemKey]['quantity']++;
            } else {
                // Add new item to cart
                $cart[$itemKey] = [
                    'nft_slug' => $nftSlug,
                    'size' => $size,
                    'quantity' => 1,
                    'price' => 0.00 // You can set actual prices later
                ];
            }

            // Save cart back to session
            session()->put('cart', $cart);

            return back()->with('status', 'Added to basket successfully!');
        })->name('cart.store');

    Route::delete('/cart/{itemKey}', function ($itemKey) {
            // Get cart from session
            $cart = session()->get('cart', []);

            // Check if item exists in cart
            if (isset($cart[$itemKey])) {
                // Remove the item
                unset($cart[$itemKey]);

                // Save updated cart back to session
                session()->put('cart', $cart);

                return back()->with('status', 'Item removed from basket');
            }

            return back()->with('error', 'Item not found in basket');
        })->name('cart.destroy');

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
