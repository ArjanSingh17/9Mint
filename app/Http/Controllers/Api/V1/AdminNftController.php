<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNftRequest;
use App\Models\Nft;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * Store a newly created NFT in storage.
     */
    public function store(StoreNftRequest $request)
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('admin')) {
            abort(403, 'Forbidden');
        }

        $data = $request->validated();

        $path = $request->file('image')->store('nfts', 'public');
        $imageUrl = Storage::url($path);

        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;

        while (Nft::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $nft = Nft::create([
            'name' => $data['name'],
            'slug' => $slug,
            'collection_id' => $data['collection_id'],
            'price_crypto' => $data['price_crypto'],
            'currency_code' => 'ETH',
            'editions_total' => $data['editions_total'],
            'editions_remaining' => $data['editions_total'],
            'image_url' => $imageUrl,
            'is_active' => true,
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
