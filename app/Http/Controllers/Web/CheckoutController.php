<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Collection;
use App\Models\Order;
use App\Models\PaymentIntent;
use App\Models\Wallet;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\WalletService;
use App\Services\Pricing\CurrencyCatalogInterface;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $creatorFeeCollection = null;
        $creatorFeeCheckout = null;
        $creatorFeeCollectionId = $request->session()->get('creator_fee_collection_id');

        if ($creatorFeeCollectionId) {
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
                    'collection_name' => $creatorFeeCollection->name,
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

        $request->session()->forget('checkout_order_id');

        return redirect('/cart')
            ->with('status', 'Order placed successfully! Order #' . $order->id);
    }

    private function storeCreatorFeeCheckout(Request $request)
    {
        $user = $request->user();
        $collectionId = $request->session()->get('creator_fee_collection_id');
        $collection = $collectionId
            ? Collection::query()
                ->where('id', $collectionId)
                ->where('submitted_by_user_id', $user->id)
                ->first()
            : null;

        if (! $collection || $collection->creation_fee_payment_state !== 'unpaid') {
            $request->session()->forget('creator_fee_collection_id');
            return redirect()->route('creator.collections.create')
                ->with('error', 'Creator fee checkout session expired. Please submit again.');
        }

        $provider = $request->input('provider');
        $payAmount = 80.00;
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
                        'collection_id' => $collection->id,
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
                'collection_id' => $collection->id,
                'amount' => $payAmount,
                'currency' => $payCurrency,
            ],
        ]);

        $order->update(['status' => 'paid']);

        $updateData['creation_fee_payment_intent_id'] = $intent->id;
        $collection->update($updateData);
        $request->session()->forget('creator_fee_collection_id');

        return redirect()->route('creator.collections.create')
            ->with('status', 'Creator fee paid. Your collection is now awaiting admin review.');
    }
}
