<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;

// FRONTEND NFT CONTROLLERS
use App\Http\Controllers\Web\CollectionController as WebCollection;
use App\Http\Controllers\Web\NftController as WebNft;

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
        // TODO: implement cart here
        return back()->with('status', 'Added to basket (stub)');
    })->name('cart.store');
});

// ------------------------------
// NEW NFT FRONTEND ROUTES
// ------------------------------

Route::get('/collections', [WebCollection::class, 'index'])->name('collections.index');
Route::get('/collections/{slug}', [WebCollection::class, 'show'])->name('collections.show');
Route::get('/collections/{collectionSlug}/{nftSlug}', [WebNft::class, 'show'])->name('nfts.show');

