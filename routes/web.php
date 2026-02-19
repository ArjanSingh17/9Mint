<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FavouriteController;
use App\Http\Controllers\CollectionPageController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// FRONTEND NFT CONTROLLERS
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\Web\CartController as WebCartController;
use App\Http\Controllers\Web\CheckoutController as WebCheckoutController;
use App\Http\Controllers\Web\CollectionController as WebCollection;
use App\Http\Controllers\Web\FavouritePageController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\NftController as WebNft;

// MODELS
use App\Models\Order;

// ------------------------------
// AUTH (GUEST)
// ------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    
    // 'throttle:6,1' -> allows 6 tries per 1 minute
    Route::post('/login', [AuthController::class, 'loginWeb'])->middleware('throttle:6,1');
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    // Consider adding throttle here too if you're worried about bot registrations
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

Route::get('/my-profile', function (Request $r) {
    if (!auth()->check()) {
        return redirect()->route('login')->with('status', 'Please log in to view your profile.');
    }
    return view('profile.show', ['user' => $r->user()]);
})->name('profile.show');


// ------------------------------
// FRONTEND TEAM OLD URL SUPPORT
// ------------------------------
Route::get('/products/Glossy-collection', function () {
    return redirect()->route('collections.show', ['slug' => 'glossy-collection']);
});

Route::get('/products/SuperheroCollection', function () {
    return redirect()->route('collections.show', ['slug' => 'superhero-collection']);
});


// ------------------------------
// NEW DYNAMIC COLLECTION ROUTE
// ------------------------------
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

    // Chat / Conversations
    Route::post('/conversations/start/{listing}', [ConversationController::class, 'start'])->name('conversations.start');
});

Route::post('send-email', [ContactController::class, 'sendEmail'])->name('send.email');

// ------------------------------
// CHAT & TICKETS (LIVEWIRE)
// ------------------------------
Route::livewire('/chat/ticket/{query}', 'pages::chat.ticket.index')->name('chat.ticket');
Route::livewire('/chat/user/{user}/{conversation}', 'pages::chat.user.index')->name('chat.user');
Route::livewire('/chat/{query}', 'pages::chat.index')->name('chat');

// ------------------------------
// ADMIN ROUTES 
// ------------------------------
Route::middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Tickets
    Route::livewire('/admin/tickets', 'pages::tickets');

    // Inventory
    Route::get('/admin/inventory', [AdminController::class, 'inventory'])->name('admin.inventory');

    // User Management
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/admin/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
});

// Reviews Management
Route::get('/reviewUs', function () {
    return view('reviewUs');
})->name('review.us');