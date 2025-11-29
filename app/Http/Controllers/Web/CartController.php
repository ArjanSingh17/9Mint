<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Nft;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    //Get all cart items for authenticated user
     
    public function index(Request $request)
    {
        $cartItems = CartItem::where('user_id', $request->user()->id)
            ->with('nft.collection')
            ->get();

        
        $formattedItems = $cartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'quantity' => $item->quantity,
                'size' => $item->size ?? 'medium',
                'nft' => [
                    'id' => $item->nft->id,
                    'slug' => $item->nft->slug,
                    'name' => $item->nft->name,
                    'description' => $item->nft->description,
                    'image_url' => $item->nft->image_url,
                    'price' => [
                        'amount' => $item->nft->price_crypto,
                        'currency' => $item->nft->currency_code
                    ],
                    'editions' => [
                        'total' => $item->nft->editions_total,
                        'remaining' => $item->nft->editions_remaining
                    ]
                ]
            ];
        });

        return response()->json([
            'data' => $formattedItems
        ]);
    }

    //ad item to cart 
    public function store(Request $request)
    {
        $request->validate([
            'nft_slug' => 'required|string|exists:nfts,slug',
            'quantity' => 'integer|min:1',
            'size' => 'nullable|string|in:small,medium,large'
        ]);

        $nftSlug = $request->input('nft_slug');
        $quantity = $request->input('quantity', 1);
        $size = $request->input('size', 'medium');

        try {
            DB::transaction(function () use ($request, $nftSlug, $quantity, $size) {
                // lock the NFT row to prevent race conditions
                $nft = Nft::where('slug', $nftSlug)->lockForUpdate()->first();

                if (!$nft) {
                    throw new \Exception('NFT not found');
                }

                $nftId = $nft->id;

                // check if an nft thats the sam e size is already in cart
                $cartItem = CartItem::where('user_id', $request->user()->id)
        ->where('nft_id', $nftId)
     ->where('size', $size)
        ->first();

                if ($cartItem) {
                    // Update quantity
                    $cartItem->quantity += $quantity;
                    $cartItem->save();
                } else {
                    // make new cart item
                    CartItem::create([
      'user_id' => $request->user()->id,
                 'nft_id' => $nftId,
               'quantity' => $quantity,
           'size' => $size
         ]);
   }
         });

        return response()->json([
         'message' => 'Item added to cart successfully'
         ]);

     } catch (\Exception $e) {
    return response()->json([
     'message' => $e->getMessage()
     ], 400);
      }
    }

   //remoce item from cart
    public function destroy(Request $request, $cartItemId)
    {
        $deleted = CartItem::where('id', $cartItemId)
            ->where('user_id', $request->user()->id)
            ->delete();
        if ($deleted) {
            return response()->json([
            'message' => 'Item removed from cart'
            ]);
        }
        return response()->json([
     'message' => 'Item not found in cart'
      ], 404);
    }
}
