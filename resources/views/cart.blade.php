
@extends('layouts.app')

@section('title', 'Your Basket')

@push('styles')
  @vite('resources/css/pages/app-pages.css')
@endpush

@section('content')
    {{-- Header --}}
    <div class="basket-page">
      <h1 class="basket-title">Your Basket</h1>

      {{-- Success --}}
      @if(session('status'))
        <div style="background: #4CAF50; color: white; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 8px; text-align: center;">
            {{ session('status') }}
        </div>
      @endif

      {{-- Error --}}
      @if(session('error'))
        <div style="background: #f44336; color: white; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 8px; text-align: center;">
            {{ session('error') }}
        </div>
      @endif

      <div class="basket-content">
        {{-- Items --}}
        <div class="basket-items">
          @php
            $subtotal = 0;
            $displayCurrency = $payCurrency ?? null;
          @endphp

          @if($cartItems->isEmpty())
            <p style="padding: 20px; text-align: center;">Your basket is empty. <a href="/products">Browse our collections</a></p>
          @else
            {{-- Cards --}}
            @foreach($cartItems as $item)
              @php
                $listing = $item->listing;
                $nft = $listing?->token?->nft;
                $quote = $quotes[$item->id] ?? null;
                $itemTotal = $quote ? ($quote['pay_amount'] * $item->quantity) : 0;
                $subtotal += $itemTotal;
                $nftName = $nft?->name ?? 'NFT';
                $imageUrl = $nft?->image_url ?? '/images/robotman.webp';
                $currency = $quote['pay_currency'] ?? ($payCurrency ?? 'GBP');
                $displayCurrency = $displayCurrency ?: $currency;
                $currencySymbol = $currencySymbols[$currency] ?? null;
              @endphp

              <div class="basket-item">
                <img
                  src="{{ asset(ltrim($imageUrl, '/')) }}"
                  class="basket-item-thumbnail"
                  alt="{{ $nftName }}"
                />

                <div class="basket-item-info">
                  <h3>{{ $nftName }}</h3>
                  <p>Listing #{{ $listing?->id }}</p>
                  @if ($listing?->ref_currency && $listing?->ref_currency !== $currency)
                    <p>Ref currency: {{ $listing->ref_currency }}</p>
                  @endif
                </div>

                <div class="basket-item-qty">
                  <span>Quantity: {{ $item->quantity }}</span>
                </div>

                <div class="basket-item-price">
                  {{ $currencySymbol ? $currencySymbol . number_format($itemTotal, 2) : number_format($itemTotal, 2) . ' ' . $currency }}
                </div>

                <div class="basket-item-remove">
                  <form method="POST" action="{{ route('cart.destroy', $item->id) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="remove-button" onclick="return confirm('Remove this item from cart?')">Remove</button>
                  </form>
                </div>
              </div>
            @endforeach
          @endif
        </div>

        {{-- Summary --}}
        <div class="basket-summary">
          <h2>Order Summary</h2>

          <div class="basket-summary-row">
            <span>Subtotal</span>
            @php
              $summaryCurrency = $displayCurrency ?? 'GBP';
              $summarySymbol = $currencySymbols[$summaryCurrency] ?? null;
            @endphp
            <span>{{ $summarySymbol ? $summarySymbol . number_format($subtotal, 2) : number_format($subtotal, 2) . ' ' . $summaryCurrency }}</span>
          </div>

          <div class="basket-summary-row">
            <span>Tax</span>
            <span>{{ $summarySymbol ? $summarySymbol . number_format(0, 2) : number_format(0, 2) . ' ' . $summaryCurrency }}</span>
          </div>

          <div class="basket-summary-row">
            <span>Discount</span>
            <span>-{{ $summarySymbol ? $summarySymbol . number_format(0, 2) : number_format(0, 2) . ' ' . $summaryCurrency }}</span>
          </div>

          <div class="basket-summary-total">
            <span>Total</span>
            <span>{{ $summarySymbol ? $summarySymbol . number_format($subtotal, 2) : number_format($subtotal, 2) . ' ' . $summaryCurrency }}</span>
          </div>

          @if(!$cartItems->isEmpty())
            <a href="/checkout" class="checkout-button">Proceed to Checkout</a>
          @else
            <a href="/products" class="checkout-button">Browse Products</a>
          @endif
        </div>
      </div>
    </div>
@endsection

