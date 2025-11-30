<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;

// FRONTEND NFT CONTROLLERS
use App\Http\Controllers\Web\CollectionController as WebCollection;
use App\Http\Controllers\Web\NftController as WebNft;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CollectionPageController;


// ------------------------------
// AUTH (GUEST)
// ------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/login', [AuthController::class, 'loginWeb']);
    Route::post('/register', [AuthController::class, 'registerWeb']);
});


// ------------------------------
// STATIC PAGES
// ------------------------------
Route::get('/', fn() => view('login-register'));
Route::get('/cart', fn() => view('cart'));
Route::get('/checkout', fn() => view('checkout'));
Route::get('/pricing', fn() => view('Pricing'));
Route::get('/contactUs', fn() => view('ContactUs'));
Route::get('/contactUs/terms', fn() => view('TermsAndConditions'));
Route::get('/contactUs/faqs', fn() => view('Faqs'));


// ------------------------------
// MAIN PAGES
// ------------------------------
Route::get('/homepage', [HomeController::class, 'index'])->name('homepage');
Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
Route::get('/aboutUs', [AboutUsController::class, 'index'])->name('about');


// ------------------------------
// FRONTEND TEAM OLD URL SUPPORT
// ------------------------------

// Old Glossy URL used in frontend
Route::get('/products/Glossy-collection', function () {
    return redirect()->route('collections.show', ['slug' => 'glossy-collection']);
});

// Old Superhero URL used in frontend
Route::get('/products/SuperheroCollection', function () {
    return redirect()->route('collections.show', ['slug' => 'superhero-collection']);
});


// ------------------------------
// NEW DYNAMIC COLLECTION ROUTE
// ------------------------------
// This handles: /products/{slug}
Route::get('/products/{slug}', [CollectionPageController::class, 'show'])
    ->where('slug', '.*')
    ->name('collections.show');


// ------------------------------
// AUTHENTICATED ROUTES
// ------------------------------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');

    Route::post('/cart', function (Request $r) {
        return back()->with('status', 'Added to basket (stub)');
    })->name('cart.store');
});


// ------------------------------
// NFT COLLECTION & NFT DETAIL (Optional Future Use)
// ------------------------------
Route::get('/collections', [WebCollection::class, 'index'])->name('collections.index');
Route::get('/collections/{slug}', [WebCollection::class, 'show'])->name('collections.show.web');
Route::get('/collections/{collectionSlug}/{nftSlug}', [WebNft::class, 'show'])->name('nfts.show');
