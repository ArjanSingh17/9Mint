<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers (Public + Auth)
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\NftController;
use App\Http\Controllers\Api\V1\FavouriteController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\PriceController;

// Admin Controllers
use App\Http\Controllers\Api\V1\AdminNftController;
use App\Http\Controllers\Api\V1\AdminCollectionController;

/*
| API Routes
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v1')->group(function () {

    /*
    | PUBLIC ROUTES
    */

    Route::get('health', fn () => ['ok' => true]);

    // Collections
    Route::get('collections', [CollectionController::class, 'index']);
    Route::get('collections/{slug}', [CollectionController::class, 'show']);

    // NFTs
    Route::get('nfts', [NftController::class, 'index']);
    Route::get('nfts/{slug}', [NftController::class, 'show']);

    // Price Conversion
    Route::get('price/convert', [PriceController::class, 'convert']);


    /*
    | AUTHENTICATED ROUTES
    */

    Route::middleware('auth:sanctum')->group(function () {

        // User Profile
        Route::get('me', [AuthController::class, 'me']);

        // Favourites
        Route::get('me/favourites', [FavouriteController::class, 'index']);
        Route::post('nfts/{nft}/favourite', [FavouriteController::class, 'toggle']);

        // Cart
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart', [CartController::class, 'store']);
        Route::delete('cart/{nft}', [CartController::class, 'destroy']);

        // Checkout
        Route::post('checkout', [CheckoutController::class, 'store']);


        /*
        | ADMIN ROUTES (authenticated only)
        */

        Route::prefix('admin')->group(function () {

            // NFT Admin CRUD
            Route::post('nfts', [AdminNftController::class, 'store']);
            Route::put('nfts/{nft}', [AdminNftController::class, 'update']);
            Route::delete('nfts/{nft}', [AdminNftController::class, 'destroy']);

            // Collection Admin CRUD
            Route::post('collections', [AdminCollectionController::class, 'store']);
            Route::put('collections/{collection}', [AdminCollectionController::class, 'update']);
            Route::delete('collections/{collection}', [AdminCollectionController::class, 'destroy']);
        });
    });
});
