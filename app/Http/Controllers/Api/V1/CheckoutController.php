<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{CartItem, Order, OrderItem, Nft};

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $user = request()->user();

        DB::transaction(function () use ($user) {
            $items = CartItem::where('user_id',$user->id)->get();
            if ($items->isEmpty()) abort(422, 'Cart is empty');

            $nftIds = $items->pluck('nft_id')->all();
            $nfts = Nft::whereIn('id', $nftIds)->lockForUpdate()->get();

            $totalCrypto = 0; $totalGbp = 0;

            foreach ($items as $ci) {
                $nft = $nfts->firstWhere('id',$ci->nft_id);
                if (!$nft || $nft->editions_remaining < $ci->quantity) {
                    abort(422, 'Out of stock');
                }
                $totalCrypto += (float)$nft->price_crypto * $ci->quantity;
            }

            $order = Order::create([
                'user_id'=>$user->id,
                'status'=>'paid',
                'currency_code'=>'ETH',
                'total_crypto'=>$totalCrypto,
                'total_gbp'=>0.00,
                'placed_at' => now(),
            ]);

            foreach ($items as $ci) {
                $nft = $nfts->firstWhere('id',$ci->nft_id);
                $nft->decrement('editions_remaining', $ci->quantity);
                OrderItem::create([
                    'order_id'=>$order->id,
                    'nft_id'=>$nft->id,
                    'quantity'=>$ci->quantity,
                    'unit_price_crypto'=>$nft->price_crypto,
                    'unit_price_gbp'=>0.00,
                ]);
            }

            CartItem::where('user_id',$user->id)->delete();
        });

        return response()->noContent();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
