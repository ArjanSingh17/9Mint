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
        $data = $request->validated();

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
