<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FavouriteController;
use App\Http\Controllers\CollectionPageController;
use App\Http\Controllers\Web\PasswordResetController;

// FRONTEND NFT CONTROLLERS
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UserProfileController;
use App\Models\User;
use App\Http\Controllers\Web\CartController as WebCartController;
use App\Http\Controllers\Web\CheckoutController as WebCheckoutController;
use App\Http\Controllers\Web\CollectionController as WebCollection;
use App\Http\Controllers\Web\CreatorCollectionController;
use App\Http\Controllers\Web\FavouritePageController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\NftController as WebNft;
use App\Models\Conversation;
use App\Models\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// ------------------------------
// AUTH (GUEST)
// ------------------------------
/*
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/login', [AuthController::class, 'loginWeb']);
    Route::post('/register', [AuthController::class, 'registerWeb']);
});
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    
    // 'throttle:6,1' -> allows 6 tries per 1 minute
    Route::post('/login', [AuthController::class, 'loginWeb'])->middleware('throttle:6,1');

    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.store');
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'registerWeb']); // You can add it here too!
});


// ------------------------------
// STATIC PAGES
// ------------------------------
Route::get('/', [HomeController::class, 'index'])->name('root');
Route::get('/cart', [WebCartController::class, 'index'])->middleware(['auth', 'not_banned'])->name('cart.index');
Route::get('/checkout', [WebCheckoutController::class, 'index'])->middleware(['auth', 'not_banned'])->name('checkout.index');
Route::get('/pricing', fn() => view('pricing'));
Route::livewire('/contactUs', 'pages::contact-us');
Route::get('/contactUs/terms', fn() => view('terms-and-conditions'));
Route::get('/contactUs/faqs', fn() => view('faqs'));
Route::get('/users', function () {return view('users');})->middleware('auth');


// ------------------------------
// MAIN PAGES
// ------------------------------
Route::get('/homepage', [HomeController::class, 'index'])->name('homepage');
Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
Route::get('/aboutUs', [AboutUsController::class, 'index'])->name('about');
Route::get('/nft/{slug}', [WebNft::class, 'show'])->name('nfts.show');

Route::get('/my-profile', function () {
    return redirect()->route('profile.settings');
})->name('profile.legacy');


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
    Route::get('/profile/settings', [AuthController::class, 'profile'])->name('profile.settings');
    Route::get('/my-favourites', [FavouritePageController::class, 'index'])->name('favourites.index');
    Route::post('/nfts/{nft}/toggle-like', [FavouriteController::class, 'toggle'])->middleware('not_banned')->name('nfts.toggle');
    Route::post('/chat/start/{receiverId}', [ConversationController::class, 'startConversation'])->middleware(['auth', 'not_banned'])->name('chat.start');
    Route::patch('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/password', [AuthController::class, 'updatePassword'])->name('password.update');
    Route::get('/chat/enter/{receiverId}', [ConversationController::class, 'enterConversation'])->middleware('auth')->name('chat.enter');

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

    Route::post('/cart', [WebCartController::class, 'store'])->middleware('not_banned')->name('cart.store');
    Route::delete('/cart/{id}', [WebCartController::class, 'destroy'])->middleware('not_banned')->name('cart.destroy');
    Route::post('/orders', [WebCheckoutController::class, 'store'])->middleware('not_banned')->name('orders.store');
    Route::get('/inventory', [InventoryController::class, 'index'])->middleware('not_banned')->name('inventory.index');
    Route::get('/listings', [InventoryController::class, 'listings'])->middleware('not_banned')->name('listings.index');
    Route::post('/inventory/listings', [InventoryController::class, 'store'])->middleware('not_banned')->name('inventory.listing.store');
    Route::delete('/inventory/listings/{listing}', [InventoryController::class, 'destroy'])->middleware('not_banned')->name('inventory.listing.destroy');
    Route::post('/conversations/start-user/{user}', [ConversationController::class, 'startWithUser'])->middleware('not_banned')->name('conversations.start-user');
    Route::get('/creator/collections/create', [CreatorCollectionController::class, 'create'])->middleware('not_banned')->name('creator.collections.create');
    Route::post('/creator/collections', [CreatorCollectionController::class, 'store'])->middleware('not_banned')->name('creator.collections.store');

    // view and update details
    //  Route::get('/profile', [UserProfileController::class, 'showSelf'])->name('profile.show');
    // Handle the form submission to update the profile
    //Route::patch('/profile', [UserProfileController::class, 'updateSelf'])->name('profile.update');

    // change Password
    //Route::patch('/profile/password', [UserProfileController::class, 'updatePassword'])->name('password.update');
});

Route::get('/inventory/{username}', [InventoryController::class, 'showByUsername'])
    ->name('inventory.show');

Route::get('/profile', function () {
    abort(404);
});

Route::get('/profile/{username}', function (string $username) {
    $profileUser = User::where('name', $username)->firstOrFail();
    $isOwner = auth()->check() && auth()->id() === $profileUser->id;

    return view('profile.show', [
        'user' => $profileUser,
        'isOwner' => $isOwner,
    ]);
})->name('profile.show');

Route::post('send-email', [ContactController::class, 'sendEmail'])->name('send.email');
Route::livewire('/chat/ticket/{query}', 'pages::chat.ticket.index')
    ->name('chat.ticket');
Route::livewire('/chat/user/{user}/{conversation}', 'pages::chat.user.index')
    ->name('chat.user');

Route::post('/conversations/start/{listing}', [ConversationController::class, 'start'])
    ->middleware(['auth', 'not_banned'])
    ->name('conversations.start');

Route::livewire('/chat/{query}', 'pages::chat.index')
    ->name('chat');

//ADMIN ROUTES 
Route::middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/approvals', [AdminController::class, 'approvals'])->name('admin.approvals.index');
    Route::get('/admin/approvals/{collection}', [AdminController::class, 'reviewCollection'])->name('admin.approvals.show');
    Route::post('/admin/collections/{collection}/approve', [AdminController::class, 'approveCollection'])->name('admin.collections.approve');
    Route::post('/admin/collections/{collection}/reject', [AdminController::class, 'rejectCollection'])->name('admin.collections.reject');
    Route::post('/admin/nfts/{nft}/approve', [AdminController::class, 'approveNft'])->name('admin.nfts.approve');
    Route::post('/admin/nfts/{nft}/reject', [AdminController::class, 'rejectNft'])->name('admin.nfts.reject');

    //tickets
    Route::livewire('/admin/tickets', 'pages::tickets');
    // Inventory
    Route::get('/admin/inventory', [AdminController::class, 'inventory'])->name('admin.inventory');
    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');

    // User Management
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users/{id}/ban', [AdminController::class, 'banUser'])->name('admin.users.ban');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    //Show the edit form
    Route::get('/admin/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    
    // Save the changes
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');

});

// Reviews Management
Route::get('/reviewUs', function () {
    return view('reviewUs');
})->name('review.us');
