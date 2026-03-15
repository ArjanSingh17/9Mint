<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\NftToken;
use App\Models\OrderItem;
use App\Models\SalesHistory;
use App\Models\User;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\WalletService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('inventory.show', ['username' => $request->user()->name]);
    }

    public function showByUsername(Request $request, string $username)
    {
        $profileUser = User::where('name', $username)->firstOrFail();
        $viewer = $request->user();
        $isOwnerInventory = $viewer && $viewer->id === $profileUser->id;

        if (! $isOwnerInventory && !($profileUser->nfts_public ?? false)) {
            return view('inventory.private', [
                'user' => $profileUser,
            ]);
        }

        $tokens = NftToken::with(['nft', 'listing'])
            ->where('owner_user_id', $profileUser->id)
            ->get();

        $paidTokenIds = [];
        $currencies = [];
        $currencyCatalog = app(CurrencyCatalogInterface::class);
        $walletService = app(WalletService::class);

        $currencies = $currencyCatalog->listEnabledCurrencies();
        if (empty($currencies)) {
            $currencies = [$currencyCatalog->defaultPayCurrency()];
        }

        $baseCurrency = strtoupper($currencyCatalog->defaultDisplayCurrency());
        if (!in_array($baseCurrency, $currencies, true)) {
            $currencies[] = $baseCurrency;
        }

        if ($isOwnerInventory) {
            $paidTokenIds = OrderItem::whereIn('token_id', $tokens->pluck('id'))
                ->whereHas('order', function ($q) use ($profileUser) {
                    $q->where('status', 'paid')
                        ->where('user_id', $profileUser->id);
                })
                ->pluck('token_id')
                ->unique()
                ->all();
        }

        $inventoryTotals = array_fill_keys($currencies, 0.0);
        $valuedTokenCount = 0;
        $tokenIds = $tokens->pluck('id')->filter()->all();
        $latestSalesByToken = SalesHistory::query()
            ->whereIn('token_id', $tokenIds)
            ->orderByDesc('sold_at')
            ->get()
            ->groupBy('token_id')
            ->map(function ($rows) {
                return $rows->first();
            });

        foreach ($tokens as $token) {
            $listing = $token->listing;
            $nft = $token->nft;
            $latestSale = $latestSalesByToken->get($token->id);

            $sourceAmount = null;
            $sourceCurrency = null;

            if ($listing && in_array($listing->status, ['active', 'reserved'], true)) {
                $sourceAmount = (float) $listing->ref_amount;
                $sourceCurrency = strtoupper((string) $listing->ref_currency);
            } elseif ($latestSale && !is_null($latestSale->pay_amount) && !empty($latestSale->pay_currency)) {
                $sourceAmount = (float) $latestSale->pay_amount;
                $sourceCurrency = strtoupper((string) $latestSale->pay_currency);
            } elseif ($nft && !is_null($nft->primary_ref_amount) && !empty($nft->primary_ref_currency)) {
                $sourceAmount = (float) $nft->primary_ref_amount;
                $sourceCurrency = strtoupper((string) $nft->primary_ref_currency);
            } elseif ($nft && !is_null($nft->price_crypto) && !empty($nft->currency_code)) {
                $sourceAmount = (float) $nft->price_crypto;
                $sourceCurrency = strtoupper((string) $nft->currency_code);
            }

            if (is_null($sourceAmount) || empty($sourceCurrency) || $sourceAmount <= 0) {
                continue;
            }

            $valuedTokenCount++;

            foreach ($currencies as $targetCurrency) {
                $targetCurrency = strtoupper((string) $targetCurrency);
                try {
                    if ($sourceCurrency === $targetCurrency) {
                        $inventoryTotals[$targetCurrency] += $sourceAmount;
                    } else {
                        $converted = $walletService->convertAmount($sourceAmount, $sourceCurrency, $targetCurrency);
                        $inventoryTotals[$targetCurrency] += (float) ($converted['amount'] ?? 0);
                    }
                } catch (\Throwable $e) {
                    // Skip unsupported conversions but keep rendering inventory.
                }
            }
        }

        $rateMatrix = [];
        foreach ($currencies as $fromCurrency) {
            $fromCurrency = strtoupper((string) $fromCurrency);
            $rateMatrix[$fromCurrency] = [];

            foreach ($currencies as $targetCurrency) {
                $targetCurrency = strtoupper((string) $targetCurrency);

                if ($fromCurrency === $targetCurrency) {
                    $rateMatrix[$fromCurrency][$targetCurrency] = 1.0;
                    continue;
                }

                try {
                    $converted = $walletService->convertAmount(1.0, $fromCurrency, $targetCurrency);
                    $rateMatrix[$fromCurrency][$targetCurrency] = (float) ($converted['amount'] ?? 0);
                } catch (\Throwable $e) {
                    $rateMatrix[$fromCurrency][$targetCurrency] = null;
                }
            }
        }

        return view('inventory.index', [
            'tokens' => $tokens,
            'currencies' => $currencies,
            'eligibleTokenIds' => $paidTokenIds,
            'inventoryUser' => $profileUser,
            'isOwnerInventory' => $isOwnerInventory,
            'inventoryTotals' => $inventoryTotals,
            'inventoryValuationBase' => $baseCurrency,
            'inventoryRateMatrix' => $rateMatrix,
            'inventoryValuedTokenCount' => $valuedTokenCount,
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
