@extends('layouts.app')

@section('title', 'Checkout')

@push('styles')
  @vite('resources/css/pages/checkout.css')
@endpush

@push('scripts')
  @vite('resources/js/page-scripts/checkout-expiry.js')
  @vite('resources/js/page-scripts/checkout-payment.js')
@endpush
       
@section('content')
    {{-- Checkout --}}
    @if($order)
      <div
        id="checkoutExpiry"
        class="checkout-expiry-banner"
        data-expires-at="{{ optional($order->expires_at)->toIso8601String() }}"
      ></div>
    @endif

    <div class="checkoutContainer {{ $order ? 'has-expiry-banner' : '' }}">
      <h1>Checkout</h1>

      {{-- Success --}}
      @if(session('status'))
        <div style="background: #4CAF50; color: white; padding: 15px; margin: 20px auto; max-width: 800px; border-radius: 8px; text-align: center;">
            {{ session('status') }}
        </div>
      @endif

      {{-- Error --}}
      @if(session('error'))
        <div style="background: #f44336; color: white; padding: 15px; margin: 20px auto; max-width: 800px; border-radius: 8px; text-align: center;">
            {{ session('error') }}
        </div>
      @endif

      @php
        $subtotal = 0;
        $displayCurrency = null;
      @endphp

      {{-- Empty --}}
      @if(!$order)
        <p style="text-align: center; padding: 40px;">Your cart is empty or checkout has expired. <a href="/products">Browse products</a></p>
      @else
        <form method="POST" action="{{ route('orders.store') }}">
          @csrf

          {{-- Shipping --}}
          <section class="checkoutSection">
            <h2>Shipping Information</h2>
            <div style="display: flex; flex-direction: column; gap: 15px;">
              <input type="text" name="full_name" placeholder="Full Name" required />
              <input type="text" name="address" placeholder="Address" required />
              <input type="text" name="city" placeholder="City" required />
              <input type="text" name="postal_code" placeholder="Postal Code" required />
            </div>
          </section>

          {{-- Summary --}}
          <section class="checkoutSection">
            <h2>Your Order</h2>

            <div style="margin-bottom: 20px;">
              @foreach($order->items as $item)
                @php
                  $listing = $item->listing;
                  $nft = $listing?->token?->nft;
                  $itemTotal = ($item->pay_unit_amount ?? 0) * $item->quantity;
                  $subtotal += $itemTotal;
                  $nftName = $nft?->name ?? 'NFT';
                  $currency = $item->pay_currency ?? ($order->pay_currency ?? 'GBP');
                  $displayCurrency = $displayCurrency ?: $currency;
                  $currencySymbol = $currencySymbols[$currency] ?? null;
                @endphp

                <div style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ddd; margin-bottom: 10px;">
                  <div>
                    <strong>{{ $nftName }}</strong><br>
                    <small>Listing #{{ $listing?->id }} | Quantity: {{ $item->quantity }}</small>
                  </div>
                  <div>
                    <strong>{{ $currencySymbol ? $currencySymbol . number_format($itemTotal, 2) : number_format($itemTotal, 2) . ' ' . $currency }}</strong>
                  </div>
                </div>
              @endforeach

              <div style="display: flex; justify-content: space-between; padding: 15px 10px; border-top: 2px solid #333; margin-top: 15px;">
                <strong>Total:</strong>
                @php
                  $summaryCurrency = $displayCurrency ?? 'GBP';
                  $summarySymbol = $currencySymbols[$summaryCurrency] ?? null;
                @endphp
                <strong>{{ $summarySymbol ? $summarySymbol . number_format($subtotal, 2) : number_format($subtotal, 2) . ' ' . $summaryCurrency }}</strong>
              </div>
            </div>
          </section>

          <section class="checkoutSection">
            <h2>Payment Method</h2>
            <div class="payment-methods">
              <label class="payment-option">
                <input type="radio" name="provider" value="mock_bank" required>
                <span class="payment-option__title">Bank Transfer</span>
                <span class="payment-option__desc">Pay directly from your bank account.</span>
              </label>
              <label class="payment-option">
                <input type="radio" name="provider" value="mock_crypto" required>
                <span class="payment-option__title">Crypto Wallet</span>
                <span class="payment-option__desc">Pay with a crypto wallet address.</span>
              </label>
              <label class="payment-option">
                <input type="radio" name="provider" value="mock_wallet" required>
                <span class="payment-option__title">9Mint Wallet</span>
                <span class="payment-option__desc">Pay using your 9Mint wallet balance.</span>
              </label>
            </div>

            <div class="payment-details is-hidden" data-provider="mock_bank">
              <h3>Bank details</h3>
              <p class="payment-instructions">Use the account details below to complete your transfer.</p>
              <div class="payment-fields">
                <input type="text" name="bank_account_name" placeholder="Account name (e.g. 9Mint Ltd)" required>
                <input type="text" name="bank_sort_code" placeholder="Sort code (e.g. 12-34-56)" required>
                <input type="text" name="bank_account_number" placeholder="Account number (e.g. 12345678)" required>
                <input type="text" name="bank_reference" placeholder="Payment reference (Order #{{ $order->id }})" required>
              </div>
            </div>

            <div class="payment-details is-hidden" data-provider="mock_crypto">
              <h3>Wallet details</h3>
              <p class="payment-instructions">Send the exact amount to the wallet address shown.</p>
              <div class="payment-fields">
                <input type="text" name="wallet_address" placeholder="Wallet address (e.g. 0x...)" required>
                <input type="text" name="wallet_tag" placeholder="Memo / Tag" required>
                <input type="text" name="wallet_network" value="{{ $order->pay_currency }}" readonly required>
              </div>
            </div>

            <div class="payment-details is-hidden" data-provider="mock_wallet">
              @php $walletBalances = $walletBalances ?? collect(); @endphp
              <h3>9Mint wallet</h3>
              <p class="payment-instructions">Select which wallet currency to pay from.</p>
              <div class="payment-fields">
                <label>
                  Wallet currency
                  <select name="wallet_currency" data-wallet-currency-select required>
                    @foreach ($walletBalances as $balance)
                      <option value="{{ $balance->currency }}" data-balance="{{ $balance->balance ?? 0 }}">
                        {{ $balance->currency }}
                      </option>
                    @endforeach
                  </select>
                </label>
                <span class="payment-wallet-balance" data-wallet-balance>
                  Balance: --
                </span>
              </div>
            </div>

            <div
              class="payment-summary is-hidden"
              data-payment-summary
              data-pay-amount="{{ $order->pay_total_amount }}"
              data-pay-currency="{{ $order->pay_currency }}"
              data-ref-amount="{{ $order->ref_total_amount }}"
              data-ref-currency="{{ $order->ref_currency }}"
            >
              <p class="payment-summary__amount" data-payment-amount>
                @php $paySymbol = $currencySymbols[$order->pay_currency ?? 'GBP'] ?? null; @endphp
                Amount due: {{ $paySymbol
                    ? $paySymbol . number_format($order->pay_total_amount ?? 0, 2)
                    : number_format($order->pay_total_amount ?? 0, 2) . ' ' . $order->pay_currency }}
              </p>
              <p class="payment-summary__hint" data-wallet-network-row>
                Network: <span data-wallet-network>ETH</span>
              </p>
              <p class="payment-summary__hint" data-conversion-text>
                @if ($order->ref_currency)
                  Conversion: {{ $order->ref_currency }} {{ number_format($order->ref_total_amount ?? 0, 2) }}
                  equals {{ $order->pay_currency }} {{ number_format($order->pay_total_amount ?? 0, 2) }} at checkout time.
                @else
                  Conversion locked at checkout time.
                @endif
              </p>
            </div>
          </section>

          <button type="submit" class="checkout-place-order">Place Order</button>
        </form>
      @endif
    </div>
@endsection
