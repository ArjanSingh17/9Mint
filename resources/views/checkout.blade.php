


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Checkout - 9Mint</title>
  <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
</head>
<body>

    <div>

        <x-navbar />

    <div class="checkoutContainer">
      <h1>Checkout</h1>


      <section class="checkoutSection">
        <h2>Shipping Information</h2>
        <form id="shippingForm">
     <input type="text" id="fullName" placeholder="Full Name" required />
        <input type="text" id="address" placeholder="Address" required />
        <input type="text" id="city" placeholder="City" required />
  <input type="text" id="postalCode" placeholder="Postal Code" required />
        </form>
      </section>


      <section class="checkoutSection">
        <h2>Your Order</h2>
        <div id="orderSummary">
    <p id="loadingMessage">Loading your order...</p>
<p id="emptyMessage" style="display: none;">No items in cart yet…</p>
     </div>
 <div id="orderItems" style="margin-top: 20px;"></div>
<div id="orderTotal" style="margin-top: 20px; font-size: 1.2em; font-weight: bold;"></div>
      </section>
 <button id="proceedBtn" onclick="placeOrder()" disabled style="padding: 15px 30px; font-size: 1.1em; cursor: pointer; background: #4CAF50; color: white; border: none; border-radius: 5px;">Proceed to Payment</button>
<div id="successMessage" style="display: none; margin-top: 20px; padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;"></div>
    </div>
    </div>

<script>
let cartItems = [];

// fetch cart items on page load
async function loadCheckout() {
    try {
        const response = await fetch('/web/cart', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Failed to load cart');
        }

        const data = await response.json();
        cartItems = data.data || [];

        displayCheckout();
    } catch (error) {
        console.error('Error loading checkout:', error);
        document.getElementById('loadingMessage').textContent = 'Error loading cart. Please refresh.';
    }
}

// Display checkout summary
function displayCheckout() {
    const loadingMessage = document.getElementById('loadingMessage');
    const emptyMessage = document.getElementById('emptyMessage');
    const orderItemsContainer = document.getElementById('orderItems');
    const orderTotalContainer = document.getElementById('orderTotal');
    const proceedBtn = document.getElementById('proceedBtn');

    loadingMessage.style.display = 'none';

    if (cartItems.length === 0) {
        emptyMessage.style.display = 'block';
        proceedBtn.disabled = true;
        proceedBtn.style.background = '#ccc';
        proceedBtn.style.cursor = 'not-allowed';
        return;
    }

    emptyMessage.style.display = 'none';

    // Display items
    let itemsHTML = '<div style="margin-top: 10px;">';
    let total = 0;

    cartItems.forEach(item => {
        const itemTotal = parseFloat(item.nft.price.amount) * item.quantity;
        total += itemTotal;

        itemsHTML += `
            <div style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee;">
                <div>
                    <strong>${item.nft.name}</strong>
                    <br>
                    <small>Size: ${item.size ? item.size.charAt(0).toUpperCase() + item.size.slice(1) : 'Medium'} | Qty: ${item.quantity}</small>
                </div>
                <div style="text-align: right;">
                    £${itemTotal.toFixed(2)}
                </div>
            </div>
        `;
    });

    itemsHTML += '</div>';
    orderItemsContainer.innerHTML = itemsHTML;

    // Display total
    orderTotalContainer.innerHTML = `Total: £${total.toFixed(2)}`;

    // Enable proceed button
    proceedBtn.disabled = false;
    proceedBtn.style.background = '#4CAF50';
    proceedBtn.style.cursor = 'pointer';
}

// Place order
async function placeOrder() {
    try {
        const proceedBtn = document.getElementById('proceedBtn');
        proceedBtn.disabled = true;
        proceedBtn.textContent = 'Processing...';

        const response = await fetch('/web/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({

            })
        });
        const data = await response.json();
        if (response.ok) {
            // show success message
            const successDiv = document.getElementById('successMessage');
            successDiv.innerHTML = `
                <h3>Order placed Successfully</h3>
                <p>Order ID: #${data.order.id}</p>
                <p>Total: £${parseFloat(data.order.total_crypto).toFixed(2)}</p>
                <p> order has been confirmed.</p>
                <a href="/cart" style="color: #155724; text-decoration: underline;">Return to Cart</a> |
                <a href="/homepage" style="color: #155724; text-decoration: underline;">Continue Shopping</a>
            `;
            successDiv.style.display = 'block';

            // Hide order summary and button
            document.getElementById('orderSummary').style.display = 'none';
            document.getElementById('orderItems').style.display = 'none';
            document.getElementById('orderTotal').style.display = 'none';
            proceedBtn.style.display = 'none';

        } else {
            alert(`Error: ${data.message || 'Failed to place order'}`);
            proceedBtn.disabled = false;
            proceedBtn.textContent = 'Proceed to Payment';
        }
    } catch (error) {
        console.error('error placing order:', error);
        alert('an error occurred.Please try again.');
        const proceedBtn = document.getElementById('proceedBtn');
        proceedBtn.disabled = false;
        proceedBtn.textContent = 'Proceed to Payment';
    }
}

// load checkout when page loads
document.addEventListener('DOMContentLoaded', loadCheckout);
</script>

</body>
</html>