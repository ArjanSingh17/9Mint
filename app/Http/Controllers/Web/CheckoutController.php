<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{CartItem, Order, OrderItem, Nft};
class CheckoutController extends Controller
{
  //create the order
    public function store(Request $request)
    {
        $user = $request->user();
        try {
         $order = DB::transaction(function () use ($user) {
            // lock nft
         $cartItems = CartItem::where('user_id', $user->id)
          ->lockForUpdate()
             ->get();

        if ($cartItems->isEmpty()) {
         throw new \Exception('Cart is empty');
             }

                $nftIds = $cartItems->pluck('nft_id')->all();
                $nfts = Nft::whereIn('id', $nftIds)->lockForUpdate()->get();

                $totalPrice = 0;

                // check stock nd calculate total
                foreach ($cartItems as $ci) {
                    $nft = $nfts->firstWhere('id', $ci->nft_id);
                if (!$nft || $nft->editions_remaining < $ci->quantity) {
                     throw new \Exception('Out of stock: ' . ($nft->name ?? 'NFT'));
                    }
                    $totalPrice += (float)$nft->price_crypto * $ci->quantity;
                }

                // make/create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => 'paid',  // Payment stub - automatically mark as paid
                    'currency_code' => 'GBP',
                    'total_crypto' => $totalPrice,
                    'total_gbp' => $totalPrice,  // Same as total_crypto since we're using GBP
                    'placed_at' => now(),
                ]);

                // create order items and decrement stock
     foreach ($cartItems as $ci) {
          $nft = $nfts->firstWhere('id', $ci->nft_id);
         $nft->decrement('editions_remaining', $ci->quantity);
            OrderItem::create([
                        'order_id' => $order->id,
                        'nft_id' => $nft->id,
                        'quantity' => $ci->quantity,
                       
                      
         ]);
 }
   // clear cart
                CartItem::where('user_id', $user->id)->delete();
                return $order;
            });

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
          'message' => $e->getMessage()
         ], 400);
     }
    }
}
