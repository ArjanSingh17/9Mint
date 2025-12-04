
@extends('layouts.app')

@section('title', 'Your Basket')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
@endpush

@section('content')
    <div class="basket-page">
      <h1 class="basket-title">Your Basket</h1>

      @if(session('status'))
        <div style="background: #4CAF50; color: white; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 8px; text-align: center;">
            {{ session('status') }}
        </div>
      @endif

      @if(session('error'))
        <div style="background: #f44336; color: white; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 8px; text-align: center;">
            {{ session('error') }}
        </div>
      @endif

      <div class="basket-content">
        <div class="basket-items">
          @php
            $cart = session()->get('cart', []);
            $subtotal = 0;
          @endphp

          @if(empty($cart))
            <p style="padding: 20px; text-align: center;">Your basket is empty. <a href="/products">Browse our collections</a></p>
          @else
            @foreach($cart as $key => $item)
              @php
                $itemTotal = $item['price'] * $item['quantity'];
                $subtotal += $itemTotal;

                // Format the NFT name from slug
                $nftName = ucwords(str_replace('-', ' ', $item['nft_slug']));

                // Try to load the NFT from the database to get the real image URL
                $nft = \App\Models\Nft::where('slug', $item['nft_slug'])->first();
                $imageUrl = $nft?->image_url ?? '/images/robotman.webp';
              @endphp

              <div class="basket-item">
                <img
                  src="{{ asset(ltrim($imageUrl, '/')) }}"
                  class="basket-item-thumbnail"
                  alt="{{ $nftName }}"
                />

                <div class="basket-item-info">
                  <h3>{{ $nftName }}</h3>
                  <p>Size: {{ ucfirst($item['size']) }}</p>
                </div>

                <div class="basket-item-qty">
                  <span>Quantity: {{ $item['quantity'] }}</span>
                </div>

                <div class="basket-item-price">£{{ number_format($itemTotal, 2) }}</div>

                <div class="basket-item-remove">
                  <form method="POST" action="{{ route('cart.destroy', $key) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="remove-button" onclick="return confirm('Remove this item from cart?')">Remove</button>
                  </form>
                </div>
              </div>
            @endforeach
          @endif
        </div>

        <div class="basket-summary">
          <h2>Order Summary</h2>

          <div class="basket-summary-row">
            <span>Subtotal</span>
            <span>£{{ number_format($subtotal, 2) }}</span>
          </div>

          <div class="basket-summary-row">
            <span>Tax</span>
            <span>£0.00</span>
          </div>

          <div class="basket-summary-row">
            <span>Discount</span>
            <span>-£0.00</span>
          </div>

          <div class="basket-summary-total">
            <span>Total</span>
            <span>£{{ number_format($subtotal, 2) }}</span>
          </div>

          @if(!empty($cart))
            <a href="/checkout"><button class="checkout-button">Proceed to Checkout</button></a>
          @else
            <a href="/products"><button class="checkout-button">Browse Products</button></a>
          @endif
        </div>
      </div>
    </div>
@endsection

