


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  <link rel="stylesheet" href="{{ asset('css/contactUs.css') }}">
</head>
<body>

    <div>
       
        <x-navbar />
  
    <div className="checkout-container">
      <h1>Checkout</h1>

     
      <section className="checkout-section">
        <h2>Shipping Information</h2>
        <form>
          <input type="text" placeholder="Full Name" />
          <input type="text" placeholder="Address" />
          <input type="text" placeholder="City" />
          <input type="text" placeholder="Postal Code" />
        </form>
      </section>

    
      <section className="checkout-section">
        <h2>Your Order</h2>
        <p>No items in cart yet…</p>
     
      </section>

      <button disabled>Proceed to Payment</button>
    </div>
    </div>
</body>
</html>