@extends('layouts.app')

@section('title', 'Checkout')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
@endpush
       
@section('content')
    {{-- Checkout --}}
    <div class="checkoutContainer">
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
        $cart = session()->get('cart', []);
        $subtotal = 0;
      @endphp

      {{-- Empty --}}
      @if(empty($cart))
        <p style="text-align: center; padding: 40px;">Your cart is empty. <a href="/products">Browse products</a></p>
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
              @foreach($cart as $key => $item)
                @php
                  $itemTotal = $item['price'] * $item['quantity'];
                  $subtotal += $itemTotal;
                  $nftName = ucwords(str_replace('-', ' ', $item['nft_slug']));
                @endphp

                <div style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ddd; margin-bottom: 10px;">
                  <div>
                    <strong>{{ $nftName }}</strong><br>
                    <small>Size: {{ ucfirst($item['size']) }} | Quantity: {{ $item['quantity'] }}</small>
                  </div>
                  <div>
                    <strong>£{{ number_format($itemTotal, 2) }}</strong>
                  </div>
                </div>
              @endforeach

              <div style="display: flex; justify-content: space-between; padding: 15px 10px; border-top: 2px solid #333; margin-top: 15px;">
                <strong>Total:</strong>
                <strong>£{{ number_format($subtotal, 2) }}</strong>
              </div>
            </div>
          </section>

          <button type="submit">Place Order</button>
        </form>
      @endif
    </div>
@endsection
