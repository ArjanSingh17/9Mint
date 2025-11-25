<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreNftRequest;
use App\Models\Nft;
use Illuminate\Support\Facades\Storage;

class AdminNftController extends Controller
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
    public function store(Request $request)
    {
        $data = $request->validate([
    'name'             => 'required|string|max:255',
    'collection_id'    => 'required|integer|exists:collections,id',
    'price_crypto'     => 'required|numeric|min:0',
    'editions_total'   => 'required|integer|min:1',
    'image'            => 'required|image|max:2048',
    'description'      => 'nullable|string',
]);

        $path = $request->file('image')->store('nfts','public');
        $data['image_url'] = Storage::url($path);

        $nft = Nft::create([
            'name' => $data['name'],
            'collection_id' => $data['collection_id'],
            'price_crypto' => $data['price_crypto'],
            'editions_total' => $data['editions_total'],
            'editions_remaining' => $data['editions_total'],
            'image_url' => $data['image_url'],
         ]);

         return response()->json(['data' => $nft], 201);
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
   public function update(Request $request, Nft $nft)
{
    $validated = $request->validate([
        'name'          => 'sometimes|string|max:255',
        'description'   => 'nullable|string',
        'price_crypto'  => 'sometimes|numeric|min:0',
        'editions_total' => 'sometimes|integer|min:1',
        'image'         => 'sometimes|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        if ($nft->image_url) {
            $old = str_replace('/storage/', '', $nft->image_url);
            Storage::disk('public')->delete($old);
        }

        $path = $request->file('image')->store('nfts', 'public');
        $validated['image_url'] = Storage::url($path);
    }

    $nft->update($validated);

    return response()->json([
        'message' => 'NFT updated successfully',
        'data'    => $nft
    ]);
}


    /**
     * Remove the specified resource from storage.
     */
   public function destroy(Nft $nft)
{
    if ($nft->image_url) {
        $old = str_replace('/storage/', '', $nft->image_url);
        Storage::disk('public')->delete($old);
    }

    $nft->delete();

    return response()->json([
        'message' => 'NFT deleted successfully'
    ]);
}

}
