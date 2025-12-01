<?php

use App\Models\Nft;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Collection;

// FRONTEND NFT CONTROLLERS
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\Web\HomeController;

// MODELS
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\CollectionPageController;
use App\Http\Controllers\Web\NftController as WebNft;
use App\Http\Controllers\Web\CollectionController as WebCollection;


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

    $totalGbp = 0;
    foreach ($cart as $item) {
        $totalGbp += $item['price'] * $item['quantity'];
    }

    if ($r->has('full_name')) {
        session()->put('shipping_info', [
            'full_name'   => $r->input('full_name'),
            'address'     => $r->input('address'),
            'city'        => $r->input('city'),
            'postal_code' => $r->input('postal_code'),
        ]);
    }

    $order = Order::create([
        'user_id'      => auth()->id(),
        'status'       => 'pending',
        'currency_code'=> 'GBP',
        'total_crypto' => 0,
        'total_gbp'    => $totalGbp,
        'placed_at'    => now(),
    ]);

    foreach ($cart as $item) {
        $collection = Collection::firstOrCreate(
            ['slug' => 'general'],
            ['name' => 'General Collection', 'description' => 'General NFTs']
        );

        $nft = Nft::firstOrCreate(
            ['slug' => $item['nft_slug']],
            [
                'collection_id'      => $collection->id,
                'name'               => ucwords(str_replace('-', ' ', $item['nft_slug'])),
                'description'        => 'NFT: ' . $item['nft_slug'],
                'image_url'          => '/images/placeholder.png',
                'currency_code'      => 'GBP',
                'price_crypto'       => 0,
                'editions_total'     => 1000,
                'editions_remaining' => 1000,
                'is_active'          => true,
            ]
        );

        OrderItem::create([
          'order_id'  => $order->id,
    'nft_id' => $nft->id,
        'quantity'        => $item['quantity'],
            'unit_price_crypto'=> 0,
            'unit_price_gbp'  => $item['price'],
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