


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
</head>
<body>

    <div>
       
        <x-navbar />
  
    <div class="checkoutContainer">
      <h1>Checkout</h1>

     
      <section class="checkoutSection">
        <h2>Shipping Information</h2>
        <form>
          <input type="text" placeholder="Full Name" />
          <input type="text" placeholder="Address" />
          <input type="text" placeholder="City" />
          <input type="text" placeholder="Postal Code" />
        </form>
      </section>


      <section class="checkoutSection">
        <h2>Your Order</h2>

        @php
          $cart = session()->get('cart', []);
          $subtotal = 0;
        @endphp

        @if(empty($cart))
          <p>No items in cart yet…</p>
        @else
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
        @endif

      </section>

      @if(empty($cart))
        <button disabled>Proceed to Payment</button>
      @else
        <button>Proceed to Payment</button>
      @endif
    </div>
    </div>
</body>
</html>