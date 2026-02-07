<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNftRequest;
use App\Models\Listing;
use App\Models\Nft;
use App\Models\NftToken;
use Illuminate\Support\Facades\DB;
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

        $nft = DB::transaction(function () use ($data, $slug, $imageUrl, $user) {
            $nft = Nft::create([
                'name' => $data['name'],
                'slug' => $slug,
                'collection_id' => $data['collection_id'],
                'editions_total' => $data['editions_total'],
                'editions_remaining' => $data['editions_total'],
                'image_url' => $imageUrl,
                'is_active' => true,
            ]);

            for ($i = 1; $i <= $data['editions_total']; $i++) {
                $token = NftToken::create([
                    'nft_id' => $nft->id,
                    'serial_number' => $i,
                    'owner_user_id' => null,
                    'status' => 'listed',
                ]);

                $listing = Listing::create([
                    'token_id' => $token->id,
                    'seller_user_id' => $user->id,
                    'status' => 'active',
                    'ref_amount' => $data['listing_ref_amount'],
                    'ref_currency' => $data['listing_ref_currency'],
                ]);

            }

            return $nft;
        });

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
