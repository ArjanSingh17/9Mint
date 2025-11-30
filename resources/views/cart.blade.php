
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Your Cart - 9Mint</title>
  <link rel="stylesheet" href="{{ asset('css/App.css') }}">
</head>
<body>
<x-navbar />

    <div class="basket-page">
      <h1 class="basket-title">Your Basket</h1>

      <div class="basket-content">
        <div class="basket-items" id="cartItems">
          <p id="loadingMessage">Loading cart...</p>
          <p id="emptyMessage" style="display: none;">Your cart is empty.</p>
        </div>

        <div class="basket-summary">
          <h2>Order Summary</h2>

          <div class="basket-summary-row">
            <span>Subtotal</span>
            <span id="subtotal">£0.00</span>
          </div>

          <div class="basket-summary-total">
            <span>Total</span>
            <span id="total">£0.00</span>
          </div>

          <a href="/checkout"><button class="checkout-button" id="checkoutBtn">Proceed to Checkout</button></a>
        </div>
      </div>
    </div>

<script>
let cartItems = [];

// Fetch cart items on page load
async function loadCart() {
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
  displayCart();
    } catch (error) {
        console.error('Error loading cart:', error);
 document.getElementById('loadingMessage').textContent = 'Error loading cart. Please refresh.';
    }
}

// show  cart items
function displayCart() {
    const cartContainer = document.getElementById('cartItems');
    const loadingMessage = document.getElementById('loadingMessage');
    const emptyMessage = document.getElementById('emptyMessage');

    loadingMessage.style.display = 'none';

    if (cartItems.length === 0) {
        emptyMessage.style.display = 'block';
        updateTotals();
        return;
    }
    emptyMessage.style.display = 'none';
    // clear items
    cartContainer.innerHTML = '';
    // add each cart item
    cartItems.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'basket-item';
        itemDiv.innerHTML = `
      <img
      src="${item.nft.image_url || '/images/default-nft.png'}"
      class="basket-item-thumbnail"
        alt="${item.nft.name}"
            />
            <div class="basket-item-info">
              <h3>${item.nft.name}</h3>



     <p>${item.nft.description || 'NFT'}</p>
     <p style="font-size: 0.9em; color: #666;">Size: ${item.size ? item.size.charAt(0).toUpperCase() + item.size.slice(1) : 'Medium'}</p>
            </div>
            <div class="basket-item-qty">
              <span>Qty: ${item.quantity}</span>
            </div>



            <div class="basket-item-price">
                £${parseFloat(item.nft.price.amount).toFixed(2)}
            </div>
            <button class="remove-button" onclick="removeFromCart(${item.id})" style="background: #dc3545; color: white; border: none; padding: 8px 16px; cursor: pointer; border-radius: 4px;">Remove</button>
        `;
        cartContainer.appendChild(itemDiv);
    });
updateTotals();
}

// calculate and update totals
function updateTotals() {
let total = 0;
cartItems.forEach(item => {
 total += parseFloat(item.nft.price.amount) * item.quantity;
    });
    const totalFixed = total.toFixed(2);
    document.getElementById('subtotal').textContent = `£${totalFixed}`;
    document.getElementById('total').textContent = `£${totalFixed}`;
    // disable checkout button if cart is empty
    document.getElementById('checkoutBtn').disabled = cartItems.length === 0;
}

// remove item from cart
async function removeFromCart(cartItemId) {
    try {
        const response = await fetch(`/web/cart/${cartItemId}`, {
         method: 'DELETE',
        headers: {
        'Accept': 'application/json'
        },
            credentials: 'same-origin'
        });

        if (response.ok) {
            // reload cart to show updated items
            await loadCart();
        } else {
            const data = await response.json();
            alert(`Error: ${data.message || 'Failed to remove item'}`);
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
        alert('An error occurred. Please try again.');
    }
}

// Load cart when page loads
document.addEventListener('DOMContentLoaded', loadCart);
</script>

</body>
</html>



