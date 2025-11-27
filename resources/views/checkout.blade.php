


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
        <p>No items in cart yet…</p>
     
      </section>

      <button disabled>Proceed to Payment</button>
    </div>
    </div>
</body>
</html>