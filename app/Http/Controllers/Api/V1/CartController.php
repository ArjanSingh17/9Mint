<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{CartItem, Nft};
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = CartItem::with('nft')->where('user_id', request()->user()->id)->get();
        return response()->json(['data'=>$items]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nft_id' => 'required|exists:nfts,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        DB::transaction(function () use ($data, $request) {
    $item = CartItem::where('user_id', $request->user()->id) 
                ->where('nft_id', $data['nft_id'])
                ->lockForUpdate()
                ->first();

            if ($item) {
                $item->update([
                    'quantity' => $data['quantity'] ?? 1
                ]);
            } else {
                CartItem::create([
                    'user_id' => $request->user()->id,
                    'nft_id' => $data['nft_id'],
                    'quantity' => $data['quantity'] ?? 1
                ]);
            }
        });

        return response()->noContent();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
         $item = CartItem::with('nft')
        ->where('user_id', request()->user()->id)
        ->where('id', $id)
        ->first();

    if (!$item) {
        return response()->json(['message' => 'Item not found'], 404);
    }

    return response()->json(['data' => $item]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
      $data = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $item = CartItem::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->lockForUpdate()
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->update([
            'quantity' => $data['quantity']
        ]);

        return response()->json(['data' => $item], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nft $nft)
    {
          DB::transaction(function () use ($nft) {
            CartItem::where('user_id', request()->user()->id)
                ->where('nft_id', $nft->id)
                ->lockForUpdate()
                ->delete();
        });

        return response()->noContent();
    }
}
