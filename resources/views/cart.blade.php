
@extends('layouts.app')

@section('title', 'Your Basket')

@push('styles')
  @vite('resources/css/pages/app-pages.css')
  <style>
    .basket-feedback-banner {
      position: fixed;
      top: var(--basket-feedback-top, 0px);
      left: 0;
      right: 0;
      z-index: 120;
      width: 100%;
      padding: 10px 16px;
      text-align: center;
      font-weight: 700;
      color: #fff;
      border-bottom: 1px solid rgba(0, 0, 0, 0.2);
      opacity: 0;
      pointer-events: none;
      transform: translateY(-8px);
      transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .basket-feedback-banner.is-visible {
      opacity: 1;
      transform: translateY(0);
    }

    .basket-feedback-banner.is-success {
      background: #16a34a;
    }

    .basket-feedback-banner.is-error {
      background: #dc2626;
    }
  </style>
@endpush

@section('content')
    <div
      id="basketFeedbackBanner"
      class="basket-feedback-banner{{ session('status') || session('error') ? ' is-visible' : '' }}{{ session('status') ? ' is-success' : '' }}{{ session('error') ? ' is-error' : '' }}"
      role="status"
      aria-live="polite"
    >
      {{ session('status') ?? session('error') }}
    </div>

    {{-- Header --}}
    <div class="basket-page">
      <h1 class="basket-title">Your Basket</h1>

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

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const banner = document.getElementById('basketFeedbackBanner');
      if (!banner || !banner.textContent.trim()) return;

      const header = document.querySelector('header');

      const updateBannerOffset = function () {
        if (!header) {
          document.documentElement.style.setProperty('--basket-feedback-top', '0px');
          return;
        }

        const rect = header.getBoundingClientRect();
        const nearTop = window.scrollY <= 12;
        const top = nearTop ? Math.max(0, Math.ceil(rect.height)) : 0;
        document.documentElement.style.setProperty('--basket-feedback-top', top + 'px');
      };

      updateBannerOffset();
      window.addEventListener('scroll', updateBannerOffset, { passive: true });
      window.addEventListener('resize', updateBannerOffset);

      window.setTimeout(function () {
        banner.classList.remove('is-visible');
      }, 4500);
    });
  </script>
@endpush

