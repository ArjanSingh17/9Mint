<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nft;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminNftController extends Controller
{
    /**
     * Store a new NFT
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

        // Store image
        $path = $request->file('image')->store('nfts', 'public');
        $data['image_url'] = Storage::url($path);

        // Create NFT
        $nft = Nft::create([
            'name'                => $data['name'],
            'slug'                => Str::slug($data['name']),
            'description'         => $data['description'] ?? null,
            'collection_id'       => $data['collection_id'],
            'price_crypto'        => $data['price_crypto'],
            'currency_code'       => 'ETH', // default from table
            'editions_total'      => $data['editions_total'],
            'editions_remaining'  => $data['editions_total'],  // start equal
            'is_active'           => true,
            'image_url'           => $data['image_url'],
        ]);

        return response()->json([
            'message' => 'NFT created successfully',
            'data'    => $nft,
        ], 201);
    }

    /**
     * Update an NFT
     */
    public function update(Request $request, Nft $nft)
    {
        $validated = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'description'      => 'nullable|string',
            'price_crypto'     => 'sometimes|numeric|min:0',
            'editions_total'   => 'sometimes|integer|min:1',
            'image'            => 'sometimes|image|max:2048',
            'is_active'        => 'sometimes|boolean',
        ]);

        // If name changes -> update slug too
        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // If editions_total changes, update editions_remaining only if NOT customized
        if (isset($validated['editions_total'])) {
            $validated['editions_remaining'] = $validated['editions_total'];
        }

        // If a new image is uploaded
        if ($request->hasFile('image')) {
            // Delete old file
            if ($nft->image_url) {
                $old = str_replace('/storage/', '', $nft->image_url);
                Storage::disk('public')->delete($old);
            }

            // Store new file
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
     * Delete NFT
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
