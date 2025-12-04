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


// MODELS
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Nft;
use App\Models\Collection;
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
Route::get('/cart', fn() => view('cart'));
Route::get('/checkout', fn() => view('checkout'));
Route::get('/pricing', fn() => view('pricing'));
Route::get('/contactUs', fn() => view('contact-us'));
Route::get('/contactUs/terms', fn() => view('terms-and-conditions'));
Route::get('/contactUs/faqs', fn() => view('faqs'));


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

    Route::patch('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/password', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::get('/orders', function (Request $r) {
        $user = $r->user();

        $orders = Order::with(['items.nft'])
            ->where('user_id', $user->id)
            ->orderByDesc('placed_at')
            ->orderByDesc('created_at')
            ->get();

        return view('orders.index', compact('orders'));
    })->name('orders.index');

    Route::post('/cart', function (Request $r) {
            $nftSlug = $r->input('nft_slug');
            $size = $r->input('size');

            if (!$size) {
                return back()->with('error', 'Please select a size before adding to basket');
            }

            // Get the cart from session or create empty array
            $cart = session()->get('cart', []);

            // Create a unique key for this item (nft + size combination)
            $cartKey = $nftSlug . '_' . $size;

            // Determine price based on size
            $prices = [
                'small' => 29.99,
                'medium' => 39.99,
                'large' => 49.99
            ];
            $price = $prices[$size] ?? 39.99;

            // If item already exists, increase quantity
            if (isset($cart[$cartKey])) {
                $cart[$cartKey]['quantity']++;
            } else {
                // Add new item to cart
                $cart[$cartKey] = [
                    'nft_slug' => $nftSlug,
                    'size' => $size,
                    'price' => $price,
                    'quantity' => 1
                ];
            }

            // Save cart back to session
            session()->put('cart', $cart);

            return back()->with('status', 'Added to basket successfully!');
        })->name('cart.store');

    Route::delete('/cart/{key}', function ($key) {
            $cart = session()->get('cart', []);

            if (isset($cart[$key])) {
                unset($cart[$key]);
                session()->put('cart', $cart);
                return back()->with('status', 'Item removed from basket');
            }

            return back()->with('error', 'Item not found in basket');
        })->name('cart.destroy');

 Route::post('/orders', function (Request $r) {

    $cart = session()->get('cart', []);
    if (empty($cart)) {
        return back()->with('error', 'Your cart is empty');
    }

    $defaultEditionsForNewNft = 5;

    $slugs = array_column($cart, 'nft_slug');
    $nftsBySlug = Nft::whereIn('slug', $slugs)->get()->keyBy('slug');

    foreach ($cart as $item) {
        $nft = $nftsBySlug[$item['nft_slug']] ?? null;
        $available = $nft?->editions_remaining ?? $defaultEditionsForNewNft;

        if ($available < $item['quantity']) {
            $name = $nft?->name ?? ucwords(str_replace('-', ' ', $item['nft_slug']));
            return back()->with('error', 'Out of stock: ' . $name);
        }
    }

    $totalGbp = 0;
    foreach ($cart as $item) {
        $totalGbp += $item['price'] * $item['quantity'];
    }

    if ($r->has('full_name')) {
        session()->put('shipping_info', [
            'full_name' => $r->input('full_name'),
            'address' => $r->input('address'),
            'city' => $r->input('city'),
            'postal_code' => $r->input('postal_code'),
        ]);
    }

    $order = Order::create([
        'user_id' => auth()->id(),
        'status' => 'pending',
        'currency_code'=> 'GBP',
        'total_crypto' => 0,
        'total_gbp' => $totalGbp,
        'placed_at' => now(),
    ]);

    foreach ($cart as $item) {
        $collection = Collection::firstOrCreate(
            ['slug' => 'general'],
            ['name' => 'General Collection', 'description' => 'General NFTs']
        );

        $nft = Nft::firstOrCreate(
            ['slug' => $item['nft_slug']],
            [
                'collection_id' => $collection->id,
                'name' => ucwords(str_replace('-', ' ', $item['nft_slug'])),
                'description' => 'NFT: ' . $item['nft_slug'],
                'image_url' => '/images/placeholder.png',
                'currency_code' => 'GBP',
                'price_crypto' => 0,
                'editions_total' => $defaultEditionsForNewNft,
                'editions_remaining' => $defaultEditionsForNewNft,
                'is_active' => true,
            ]
        );

        if ($nft) {
            $nft->decrement('editions_remaining', $item['quantity']);
        }

        OrderItem::create([
            'order_id' => $order->id,
            'nft_id' => $nft->id,
            'quantity' => $item['quantity'],
            'unit_price_crypto' => 0,
            'unit_price_gbp' => $item['price'],
        ]);
    }

    session()->forget('cart');
    return redirect('/cart')
        ->with('status', 'Order placed successfully! Order #' . $order->id);

})->name('orders.store');

    // view and update details
  //  Route::get('/profile', [UserProfileController::class, 'showSelf'])->name('profile.show');
    // Handle the form submission to update the profile
    //Route::patch('/profile', [UserProfileController::class, 'updateSelf'])->name('profile.update');

    // change Password
    //Route::patch('/profile/password', [UserProfileController::class, 'updatePassword'])->name('password.update');
});

 Route::post('send-email',[ContactController::class,'sendEmail'])->name('send.email');