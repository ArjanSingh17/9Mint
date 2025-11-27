
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
</head>
<body>
<x-navbar /> 

    <div class="basket-page">
      <h1 class="basket-title">Your Basket</h1>

      <div class="basket-content">
        <div class="basket-items">
          <div class="basket-item">
            <img
              src="/images/robotman.webp"
              class="basket-item-thumbnail"
              alt="Robot Man NFTs Collection"
            />

            <div class="basket-item-info">
              <h3>Robot Man NFTs Collection</h3>
              <p>NFT collection</p>
            </div>

            <div class="basket-item-qty">
              <button class="qty-button">-</button>
              <span>1</span>
              <button class="qty-button">+</button>
            </div>

            <div class="basket-item-price">£0.00</div>
          </div>
        </div>

        <div class="basket-summary">
          <h2>Order Summary</h2>

          <div class="basket-summary-row">
            <span>Subtotal</span>
            <span>£0.00</span>
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
            <span>£0.00</span>
          </div>

          <button class="checkout-button">Proceed to Checkout</button>
        </div>
      </div>
    </div>
</body>
</html>



