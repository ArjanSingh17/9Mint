<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\NftToken;
use App\Models\OrderItem;
use App\Services\Pricing\CurrencyCatalogInterface;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tokens = NftToken::with(['nft', 'listing'])
            ->where('owner_user_id', $user->id)
            ->get();

        $paidTokenIds = OrderItem::whereIn('token_id', $tokens->pluck('id'))
            ->whereHas('order', function ($q) use ($user) {
                $q->where('status', 'paid')
                    ->where('user_id', $user->id);
            })
            ->pluck('token_id')
            ->unique()
            ->all();

        $currencies = app(CurrencyCatalogInterface::class)->listEnabledCurrencies();

        return view('inventory.index', [
            'tokens' => $tokens,
            'currencies' => $currencies,
            'eligibleTokenIds' => $paidTokenIds,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'token_id' => ['required', 'integer', 'exists:nft_tokens,id'],
            'ref_amount' => ['required', 'numeric', 'min:0'],
            'ref_currency' => ['required', 'string', 'max:10'],
        ]);

        $token = NftToken::where('id', $data['token_id'])
            ->where('owner_user_id', $request->user()->id)
            ->firstOrFail();

        $isPaid = OrderItem::where('token_id', $token->id)
            ->whereHas('order', function ($q) use ($request) {
                $q->where('status', 'paid')
                    ->where('user_id', $request->user()->id);
            })
            ->exists();

        if (! $isPaid) {
            return back()->with('error', 'Only paid NFTs can be listed for resale.');
        }

        $existing = Listing::where('token_id', $token->id)
            ->whereIn('status', ['active', 'reserved'])
            ->first();

        if ($existing) {
            return back()->with('error', 'This token is already listed.');
        }

        $listing = Listing::create([
            'token_id' => $token->id,
            'seller_user_id' => $request->user()->id,
            'status' => 'active',
            'ref_amount' => $data['ref_amount'],
            'ref_currency' => strtoupper($data['ref_currency']),
        ]);

        $token->update(['status' => 'listed']);

        return back()->with('status', 'Listing created.');
    }

    public function destroy(Request $request, Listing $listing)
    {
        $user = $request->user();

        if ($listing->seller_user_id !== $user->id) {
            return back()->with('error', 'You can only unlist your own listings.');
        }

        if (! in_array($listing->status, ['active', 'reserved'], true)) {
            return back()->with('error', 'Listing cannot be unlisted.');
        }

        $listing->update([
            'status' => 'cancelled',
            'reserved_until' => null,
            'reserved_by_user_id' => null,
        ]);

        if ($listing->token && $listing->token->owner_user_id === $user->id) {
            $listing->token->update(['status' => 'owned']);
        }

        return back()->with('status', 'Listing removed.');
    }
}
