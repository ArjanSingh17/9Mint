<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\NftController;
use App\Http\Controllers\Api\V1\FavouriteController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ListingsController;
use App\Http\Controllers\Api\V1\MarketController;
use App\Http\Controllers\Api\V1\QuotesController;
use App\Http\Controllers\Api\V1\PriceController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\AdminNftController;
use App\Http\Controllers\Api\V1\ReviewController;
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    // Public
    Route::get('health', fn () => ['ok' => true]);
    Route::get('collections', [CollectionController::class, 'index']);
    Route::get('collections/{slug}', [CollectionController::class, 'show']);
    Route::get('nfts', [NftController::class, 'index']);
    Route::get('nfts/{slug}', [NftController::class, 'show']);
    Route::get('nfts/{slug}/market', [MarketController::class, 'market']);
    Route::get('nfts/{slug}/history', [MarketController::class, 'history']);
    Route::get('listings', [ListingsController::class, 'index']);
    Route::get('listings/{id}', [ListingsController::class, 'show']);
    Route::get('quotes', [QuotesController::class, 'show']);
    Route::post('quotes/bulk', [QuotesController::class, 'bulk']);
    Route::get('convert', [PriceController::class, 'convert']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('/reviews', [ReviewController::class, 'store']);
Route::get('/reviews/high', [ReviewController::class, 'highRated']);
    
    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);

        Route::get('me/favourites', [FavouriteController::class, 'index']);
        Route::post('nfts/{nft}/favourite', [FavouriteController::class, 'toggle']);

        // Cart routes
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart', [CartController::class, 'store']);
        Route::delete('cart/{id}', [CartController::class, 'destroy']);

        // Checkout routes
        Route::get('checkout', [CheckoutController::class, 'index']);
        Route::post('checkout', [CheckoutController::class, 'store']);
        Route::get('checkout/{id}', [CheckoutController::class, 'show']);
        Route::delete('checkout/{id}', [CheckoutController::class, 'destroy']);
        Route::post('checkout/{id}/pay', [PaymentController::class, 'pay']);

        // Listings (resale)
        Route::post('listings', [ListingsController::class, 'store']);
        Route::delete('listings/{id}', [ListingsController::class, 'destroy']);

        // Admin
        Route::prefix('admin')->group(function () {
            Route::post('nfts', [AdminNftController::class, 'store']);

            Route::get('/reviewUs', function () {
    return view('reviewUs');
});

        });
    });
});
