<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use App\Models\NftToken;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ListingsController extends Controller
{
    public function index()
    {
        $listings = Listing::with('token.nft')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->get();

        return ListingResource::collection($listings);
    }

    public function show(string $id)
    {
        $listing = Listing::with('token.nft')
            ->where('id', $id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->firstOrFail();

        return new ListingResource($listing);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'token_id' => ['required', 'integer', 'exists:nft_tokens,id'],
            'ref_amount' => ['required', 'numeric', 'min:0'],
            'ref_currency' => ['required', 'string', 'max:10'],
        ]);

        $token = NftToken::where('id', $data['token_id'])
            ->where('owner_user_id', $user->id)
            ->firstOrFail();

        $isPaid = OrderItem::where('token_id', $token->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('status', 'paid')
                    ->where('user_id', $user->id);
            })
            ->exists();

        if (! $isPaid) {
            abort(422, 'Only paid NFTs can be listed for resale');
        }

        $existing = Listing::where('token_id', $token->id)
            ->whereIn('status', ['active', 'reserved'])
            ->first();

        if ($existing) {
            abort(422, 'Token is already listed');
        }

        $listing = Listing::create([
            'token_id' => $token->id,
            'seller_user_id' => $user->id,
            'status' => 'active',
            'ref_amount' => $data['ref_amount'],
            'ref_currency' => strtoupper($data['ref_currency']),
        ]);

        $token->update(['status' => 'listed']);

        return response()->json(['data' => $listing->load('token.nft')], 201);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $listing = Listing::where('id', $id)
            ->where('seller_user_id', $user->id)
            ->whereIn('status', ['active', 'reserved'])
            ->firstOrFail();

        $listing->update([
            'status' => 'cancelled',
            'reserved_until' => null,
            'reserved_by_user_id' => null,
        ]);

        if ($listing->token && $listing->token->owner_user_id === $user->id) {
            $listing->token->update(['status' => 'owned']);
        }

        return response()->json(['message' => 'Listing cancelled']);
    }
}
