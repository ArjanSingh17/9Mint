<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Collection;
use App\Models\Nft;
use App\Models\Order;
use App\Models\PaymentIntent;
use App\Models\Wallet;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\WalletService;
use App\Services\Pricing\CurrencyCatalogInterface;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorFeeCollection = null;
        $creatorFeeCheckout = null;
        $creatorFeeDraft = $request->session()->get('creator_fee_draft');
        $creatorFeeCollectionId = $request->session()->get('creator_fee_collection_id');

        if (is_array($creatorFeeDraft) && ! empty($creatorFeeDraft['id'])) {
            $creatorFeeCheckout = (object) [
                'draft_id' => $creatorFeeDraft['id'],
                'collection_id' => null,
                'collection_name' => $creatorFeeDraft['name'] ?? 'Pending collection',
                'nft_count' => count($creatorFeeDraft['nfts'] ?? []),
                'pay_currency' => 'GBP',
                'pay_total_amount' => (float) ($creatorFeeDraft['creation_fee_amount_gbp'] ?? 80.00),
                'ref_currency' => 'GBP',
                'ref_total_amount' => (float) ($creatorFeeDraft['creation_fee_amount_gbp'] ?? 80.00),
            ];
        } elseif ($creatorFeeCollectionId) {
            $creatorFeeCollection = Collection::query()
                ->where('id', $creatorFeeCollectionId)
                ->where('submitted_by_user_id', $user->id)
                ->first();

            if (! $creatorFeeCollection || $creatorFeeCollection->creation_fee_payment_state !== 'unpaid') {
                $request->session()->forget('creator_fee_collection_id');
                $creatorFeeCollection = null;
            } else {
                $creatorFeeCheckout = (object) [
                    'collection_id' => $creatorFeeCollection->id,
                    'draft_id' => null,
                    'collection_name' => $creatorFeeCollection->name,
                    'nft_count' => $creatorFeeCollection->nfts()->count(),
                    'pay_currency' => 'GBP',
                    'pay_total_amount' => 80.00,
                    'ref_currency' => 'GBP',
                    'ref_total_amount' => 80.00,
                ];
            }
        }

        $order = null;
        if (! $creatorFeeCheckout) {
            $orderId = $request->session()->get('checkout_order_id');
            if ($orderId) {
                $order = Order::with('items.listing.token.nft')
                    ->where('id', $orderId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($order && $order->expires_at && $order->expires_at->isPast()) {
                    $request->session()->forget('checkout_order_id');
                    $order = null;
                }
            }

            if (! $order) {
                $items = CartItem::with('listing.token.nft')
                    ->where('user_id', $user->id)
                    ->get();

                if ($items->isNotEmpty()) {
                    $payCurrency = app(CurrencyCatalogInterface::class)->defaultPayCurrency();
                    $order = app(CheckoutService::class)->createOrderFromCart($user, $payCurrency);
                    $request->session()->put('checkout_order_id', $order->id);
                }
            }
        }

        $currencyCatalog = app(CurrencyCatalogInterface::class);
        $enabledCurrencies = $currencyCatalog->listEnabledCurrencies();
        $balances = Schema::hasTable('wallets')
            ? Wallet::where('user_id', $user->id)->get()->keyBy('currency')
            : collect();
        $walletBalances = collect($enabledCurrencies)->map(function ($currency) use ($balances) {
            $balance = $balances[$currency]->balance ?? 0;
            return (object) ['currency' => $currency, 'balance' => (float) $balance];
        });

        return view('checkout', [
            'order' => $order?->load('items.listing.token.nft'),
            'walletBalances' => $walletBalances,
            'creatorFeeCollection' => $creatorFeeCollection,
            'creatorFeeCheckout' => $creatorFeeCheckout,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:40'],
            'provider' => ['required', 'in:mock_bank,mock_crypto,mock_wallet'],
            'bank_account_name' => ['required_if:provider,mock_bank', 'string', 'max:255'],
            'bank_sort_code' => ['required_if:provider,mock_bank', 'string', 'max:50'],
            'bank_account_number' => ['required_if:provider,mock_bank', 'string', 'max:50'],
            'bank_reference' => ['required_if:provider,mock_bank', 'string', 'max:255'],
            'wallet_address' => ['required_if:provider,mock_crypto', 'string', 'max:255'],
            'wallet_tag' => ['required_if:provider,mock_crypto', 'string', 'max:255'],
            'wallet_network' => ['required_if:provider,mock_crypto', 'string', 'max:20'],
            'wallet_currency' => ['required_if:provider,mock_wallet', 'string', 'max:10'],
        ]);

        if ($request->input('checkout_context') === 'creator_fee') {
            return $this->storeCreatorFeeCheckout($request);
        }

        $orderId = $request->session()->get('checkout_order_id');
        $order = $orderId
            ? Order::where('id', $orderId)->where('user_id', $request->user()->id)->first()
            : null;

        if (! $order) {
            return redirect('/cart')->with('error', 'Checkout session expired. Please try again.');
        }

        if ($order->expires_at && $order->expires_at->isPast()) {
            $request->session()->forget('checkout_order_id');
            return redirect('/cart')->with('error', 'Checkout expired. Please try again.');
        }

        if ($request->input('provider') === 'mock_wallet') {
            $walletCurrency = strtoupper($request->input('wallet_currency'));
            $payAmount = (float) ($order->pay_total_amount ?? 0);
            $payCurrency = strtoupper($order->pay_currency ?? 'GBP');

            $walletService = app(WalletService::class);
            $conversion = $walletService->convertAmount($payAmount, $payCurrency, $walletCurrency);
            $debitAmount = (float) $conversion['amount'];

            try {
                $walletService->debit($request->user()->id, $walletCurrency, $debitAmount, [
                    'order_id' => $order->id,
                    'fx_provider' => $conversion['fx_provider'],
                    'fx_rate' => [
                        'from' => $payCurrency,
                        'to' => $walletCurrency,
                        'rate' => $conversion['fx_rate'],
                    ],
                    'fx_rated_at' => $conversion['fx_rated_at'],
                    'metadata' => [
                        'source' => 'wallet_checkout',
                        'pay_currency' => $payCurrency,
                        'pay_amount' => $payAmount,
                    ],
                ]);
            } catch (\RuntimeException $e) {
                return back()->with('error', 'Insufficient wallet balance for this purchase.');
            }
        }

        app(PaymentService::class)->simulatePayment(
            $order->load('items.listing.token'),
            $request->user(),
            $request->input('provider'),
            'success'
        );

        $purchasedListingIds = $order->items->pluck('listing_id')->filter()->all();
        if (! empty($purchasedListingIds)) {
            CartItem::where('user_id', $request->user()->id)
                ->whereIn('listing_id', $purchasedListingIds)
                ->delete();
        }

        $request->session()->forget('checkout_order_id');

        return redirect('/cart')
            ->with('status', 'Order placed successfully! Order #' . $order->id);
    }

    private function storeCreatorFeeCheckout(Request $request)
    {
        $user = $request->user();
        $creatorFeeDraft = $request->session()->get('creator_fee_draft');
        $collectionId = $request->session()->get('creator_fee_collection_id');
        $collection = $collectionId
            ? Collection::query()
                ->where('id', $collectionId)
                ->where('submitted_by_user_id', $user->id)
                ->first()
            : null;

        $isDraftCheckout = is_array($creatorFeeDraft) && ! empty($creatorFeeDraft['id']);
        $isLegacyCollectionCheckout = $collection && $collection->creation_fee_payment_state === 'unpaid';

        if (! $isDraftCheckout && ! $isLegacyCollectionCheckout) {
            $request->session()->forget('creator_fee_draft');
            $request->session()->forget('creator_fee_collection_id');
            return redirect()->route('creator.collections.create')
                ->with('error', 'Creator fee checkout session expired. Please submit again.');
        }

        $provider = $request->input('provider');
        $payAmount = $isDraftCheckout
            ? (float) ($creatorFeeDraft['creation_fee_amount_gbp'] ?? 80.00)
            : 80.00;
        $payCurrency = 'GBP';

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'pay_currency' => $payCurrency,
            'pay_total_amount' => $payAmount,
            'ref_currency' => $payCurrency,
            'ref_total_amount' => $payAmount,
            'fx_rate' => ['GBP' => 1],
            'fx_rated_at' => now(),
            'placed_at' => now(),
            'expires_at' => now()->addMinutes(10),
            'checkout_token' => (string) Str::uuid(),
        ]);

        $updateData = [
            'creation_fee_payment_state' => 'paid_unheld',
            'creation_fee_refund_state' => 'none',
            'creation_fee_order_id' => $order->id,
            'creation_fee_provider' => $provider,
            'creation_fee_amount_gbp' => $payAmount,
        ];

        if ($provider === 'mock_wallet') {
            $walletCurrency = strtoupper((string) $request->input('wallet_currency'));
            $walletService = app(WalletService::class);
            $conversion = $walletService->convertAmount($payAmount, $payCurrency, $walletCurrency);

            try {
                $hold = $walletService->placeHold(
                    $user->id,
                    $walletCurrency,
                    (float) $conversion['amount'],
                    [
                        'order_id' => $order->id,
                        'collection_id' => $collection?->id,
                        'creator_fee_draft_id' => $creatorFeeDraft['id'] ?? null,
                        'fx_provider' => $conversion['fx_provider'],
                        'fx_rate' => [
                            'from' => $payCurrency,
                            'to' => $walletCurrency,
                            'rate' => $conversion['fx_rate'],
                        ],
                        'fx_rated_at' => $conversion['fx_rated_at'],
                        'pay_currency' => $payCurrency,
                        'pay_amount' => $payAmount,
                    ]
                );
            } catch (\RuntimeException $e) {
                return back()->with('error', 'Insufficient wallet balance for creator fee hold.');
            }

            $updateData['creation_fee_payment_state'] = 'held_wallet';
            $updateData['creation_fee_hold_currency'] = $walletCurrency;
            $updateData['creation_fee_hold_amount'] = (float) $conversion['amount'];
            $updateData['creation_fee_hold_reference'] = $hold['hold_reference'];
        } else {
            $updateData['creation_fee_hold_currency'] = null;
            $updateData['creation_fee_hold_amount'] = null;
            $updateData['creation_fee_hold_reference'] = null;
        }

        $intent = PaymentIntent::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'status' => 'captured',
            'metadata' => [
                'context' => 'creator_fee',
                'collection_id' => $collection?->id,
                'creator_fee_draft_id' => $creatorFeeDraft['id'] ?? null,
                'collection_name' => $creatorFeeDraft['name'] ?? $collection?->name,
                'nft_count' => $isDraftCheckout
                    ? count($creatorFeeDraft['nfts'] ?? [])
                    : ($collection ? $collection->nfts()->count() : null),
                'amount' => $payAmount,
                'currency' => $payCurrency,
            ],
        ]);

        $order->update(['status' => 'paid']);

        $updateData['creation_fee_payment_intent_id'] = $intent->id;

        if ($isDraftCheckout) {
            $createdCollection = $this->createCollectionFromPaidDraft($creatorFeeDraft, $user, $updateData);
            $this->cleanupCreatorDraftAssets($creatorFeeDraft);

            $request->session()->forget('creator_fee_draft');
            $request->session()->forget('creator_fee_collection_id');

            return redirect()->route('creator.collections.create')
                ->with('status', 'Creator fee paid. Collection "' . $createdCollection->name . '" is now awaiting admin review.');
        }

        $collection->update($updateData);
        $request->session()->forget('creator_fee_collection_id');

        return redirect()->route('creator.collections.create')
            ->with('status', 'Creator fee paid. Your collection is now awaiting admin review.');
    }

    private function createCollectionFromPaidDraft(array $draft, $user, array $feeData): Collection
    {
        return DB::transaction(function () use ($draft, $user, $feeData) {
            $collection = Collection::create([
                'slug' => $this->uniqueCollectionSlug((string) ($draft['name'] ?? 'collection')),
                'name' => $draft['name'] ?? 'Untitled collection',
                'description' => $draft['description'] ?? null,
                'cover_image_url' => null,
                'creator_name' => $user->name,
                'submitted_by_user_id' => $user->id,
                'approval_status' => Collection::APPROVAL_PENDING,
                'is_public' => false,
                'creation_fee_payment_state' => $feeData['creation_fee_payment_state'] ?? 'paid_unheld',
                'creation_fee_refund_state' => $feeData['creation_fee_refund_state'] ?? 'none',
                'creation_fee_order_id' => $feeData['creation_fee_order_id'] ?? null,
                'creation_fee_payment_intent_id' => $feeData['creation_fee_payment_intent_id'] ?? null,
                'creation_fee_provider' => $feeData['creation_fee_provider'] ?? null,
                'creation_fee_amount_gbp' => $feeData['creation_fee_amount_gbp'] ?? 80.00,
                'creation_fee_hold_currency' => $feeData['creation_fee_hold_currency'] ?? null,
                'creation_fee_hold_amount' => $feeData['creation_fee_hold_amount'] ?? null,
                'creation_fee_hold_reference' => $feeData['creation_fee_hold_reference'] ?? null,
            ]);

            $collectionFolder = $collection->uploadFolderName();

            if (! empty($draft['cover_image_temp_path'])) {
                $coverImageUrl = $this->moveDraftAssetToCollectionFolder(
                    (string) $draft['cover_image_temp_path'],
                    $collectionFolder,
                    'cover'
                );
                $collection->update(['cover_image_url' => $coverImageUrl]);
            }

            $draftNfts = array_values($draft['nfts'] ?? []);
            foreach ($draftNfts as $index => $nftInput) {
                $imageUrl = $this->moveDraftAssetToCollectionFolder(
                    (string) ($nftInput['image_temp_path'] ?? ''),
                    $collectionFolder,
                    'nft-' . ($index + 1)
                );

                Nft::create([
                    'collection_id' => $collection->id,
                    'slug' => $this->uniqueNftSlug((string) ($nftInput['name'] ?? ('NFT ' . ($index + 1)))),
                    'name' => $nftInput['name'] ?? ('NFT ' . ($index + 1)),
                    'description' => $nftInput['description'] ?? null,
                    'image_url' => $imageUrl,
                    'editions_total' => (int) ($nftInput['editions_total'] ?? 1),
                    'editions_remaining' => (int) ($nftInput['editions_total'] ?? 1),
                    'primary_ref_amount' => (float) ($nftInput['ref_amount'] ?? 0.01),
                    'primary_ref_currency' => strtoupper((string) ($draft['ref_currency'] ?? 'GBP')),
                    'is_active' => false,
                    'submitted_by_user_id' => $user->id,
                    'approval_status' => Nft::APPROVAL_PENDING,
                ]);
            }

            return $collection;
        });
    }

    private function moveDraftAssetToCollectionFolder(string $tempPath, string $collectionFolder, string $namePrefix): string
    {
        if ($tempPath === '' || ! Storage::disk('local')->exists($tempPath)) {
            abort(422, 'Collection draft files are missing. Please submit the collection again.');
        }

        $sourcePath = Storage::disk('local')->path($tempPath);
        $targetDirectory = public_path("images/nfts/{$collectionFolder}");
        if (! File::exists($targetDirectory)) {
            File::makeDirectory($targetDirectory, 0755, true);
        }

        $extension = strtolower((string) pathinfo($tempPath, PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = 'png';
        }
        $filename = $namePrefix . '-' . Str::uuid() . '.' . $extension;
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;

        if (! File::copy($sourcePath, $targetPath)) {
            abort(500, 'Failed to prepare collection assets for submission.');
        }

        return "/images/nfts/{$collectionFolder}/{$filename}";
    }

    private function cleanupCreatorDraftAssets(array $draft): void
    {
        if (! empty($draft['id'])) {
            Storage::disk('local')->deleteDirectory('creator-drafts/' . $draft['id']);
        }
    }

    private function uniqueCollectionSlug(string $name): string
    {
        $base = Str::slug($name);
        $root = $base !== '' ? $base : 'collection';
        $slug = $root;
        $i = 1;

        while (Collection::where('slug', $slug)->exists()) {
            $slug = $root . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function uniqueNftSlug(string $name): string
    {
        $base = Str::slug($name);
        $root = $base !== '' ? $base : 'nft';
        $slug = $root;
        $i = 1;

        while (Nft::where('slug', $slug)->exists()) {
            $slug = $root . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
