<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;

// FRONTEND NFT CONTROLLERS
use App\Http\Controllers\Web\CollectionController as WebCollection;
use App\Http\Controllers\Web\NftController as WebNft;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\CartController as WebCartController;
use App\Http\Controllers\Web\CheckoutController as WebCheckoutController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CollectionPageController;
use App\Http\Controllers\Web\FavouritePageController;
use App\Http\Controllers\Api\V1\FavouriteController;


// MODELS
use App\Models\Order;
use App\Http\Controllers\ContactController;


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
Route::get('/', [HomeController::class, 'index'])->name('root');
Route::get('/cart', [WebCartController::class, 'index'])->middleware('auth')->name('cart.index');
Route::get('/checkout', [WebCheckoutController::class, 'index'])->middleware('auth')->name('checkout.index');
Route::get('/pricing', fn() => view('pricing'));
Route::livewire('/contactUs', 'pages::contact-us');
Route::get('/contactUs/terms', fn() => view('terms-and-conditions'));
Route::get('/contactUs/faqs', fn() => view('faqs'));


// ------------------------------
// MAIN PAGES
// ------------------------------
Route::get('/homepage', [HomeController::class, 'index'])->name('homepage');
Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
Route::get('/aboutUs', [AboutUsController::class, 'index'])->name('about');
Route::get('/nft/{slug}', [WebNft::class, 'show'])->name('nfts.show');


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
    Route::get('/my-favourites', [FavouritePageController::class, 'index'])->name('favourites.index');
    Route::post('/nfts/{nft}/toggle-like', [FavouriteController::class, 'toggle'])->name('nfts.toggle');

    Route::patch('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/password', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::get('/orders', function (Request $r) {
        $user = $r->user();

        $orders = Order::with(['items.listing.token.nft'])
            ->where('user_id', $user->id)
            ->orderByDesc('placed_at')
            ->orderByDesc('created_at')
            ->get();

        $sales = \App\Models\SalesHistory::with(['listing.token.nft', 'order'])
            ->whereHas('listing', function ($q) use ($user) {
                $q->where('seller_user_id', $user->id);
            })
            ->orderByDesc('sold_at')
            ->get();

        return view('orders.index', compact('orders', 'sales'));
    })->name('orders.index');

    Route::post('/cart', [WebCartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/{id}', [WebCartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/orders', [WebCheckoutController::class, 'store'])->name('orders.store');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/listings', [InventoryController::class, 'store'])->name('inventory.listing.store');
    Route::delete('/inventory/listings/{listing}', [InventoryController::class, 'destroy'])->name('inventory.listing.destroy');

    // view and update details
  //  Route::get('/profile', [UserProfileController::class, 'showSelf'])->name('profile.show');
    // Handle the form submission to update the profile
    //Route::patch('/profile', [UserProfileController::class, 'updateSelf'])->name('profile.update');

    // change Password
    //Route::patch('/profile/password', [UserProfileController::class, 'updatePassword'])->name('password.update');
});

 Route::post('send-email',[ContactController::class,'sendEmail'])->name('send.email');
Route::livewire('/chat/{query}', 'pages::chat.index')
    ->name('chat');