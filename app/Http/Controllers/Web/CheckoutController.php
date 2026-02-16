<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Wallet;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\WalletService;
use App\Services\Pricing\CurrencyCatalogInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $order = null;
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
            'wallet_currency' => ['required_if:provider,mock_wallet', 'string', 'max:10'],
        ]);

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
}
