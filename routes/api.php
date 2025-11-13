<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\NftController;
use App\Http\Controllers\Api\V1\FavouriteController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\PriceController;
use App\Http\Controllers\Api\V1\AdminNftController;

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
    Route::get('price/convert', [PriceController::class, 'convert']);

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);

        Route::get('me/favourites', [FavouriteController::class, 'index']);
        Route::post('nfts/{nft}/favourite', [FavouriteController::class, 'toggle']);

        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart', [CartController::class, 'store']);
        Route::delete('cart/{nft}', [CartController::class, 'destroy']);

        Route::post('checkout', [CheckoutController::class, 'store']);

        // Admin
        Route::prefix('admin')->group(function () {
            Route::post('nfts', [AdminNftController::class, 'store']);
        });
    });
});
