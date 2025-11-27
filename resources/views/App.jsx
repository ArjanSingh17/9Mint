import { useState } from "react";
import "./App.css";

function App() {
  const [page, setPage] = useState("auth");

  return (
    <>
      <nav className="navbar">
        <div className="logo-container">
          <img
            src="/images/9mints.webp"
            alt="9 Mint Logo"
            className="logo-image"
          />
        </div>

        <div className="nav-links">
          <a href="#">HomePage</a>
          <a href="#">AboutUs</a>
          <a href="#">Product</a>
          <a href="#">Pricing</a>
          <a href="#">Contact</a>
        </div>

        <div className="nav-auth">
          <button className="basket-btn" onClick={() => setPage("basket")}>
            <span className="basket-icon">🛒</span>
            <span className="basket-badge">1</span>
          </button>

          <button className="nav-btn signin" onClick={() => setPage("auth")}>
            Account
          </button>
        </div>
      </nav>

      {page === "auth" ? <AuthSection /> : <BasketPage />}
    </>
  );
}

function AuthSection() {
  return (
    <div className="auth-section">
      <div className="auth-form">
        <h2>Login</h2>
        <input type="email" placeholder="Email" />
        <input type="password" placeholder="Password" />
        <button>Login</button>
        <a className="forgot-password" href="#">Forgot Password?</a>
      </div>

      <div className="auth-form">
        <h2>Register</h2>
        <input type="text" placeholder="Full Name" />
        <input type="email" placeholder="Email" />
        <input type="password" placeholder="Password" />
        <button>Register</button>
      </div>
    </div>
  );
}

function BasketPage() {
  return (
    <div className="basket-page">
      <h1 className="basket-title">Your Basket</h1>

      <div className="basket-content">
        <div className="basket-items">
          <div className="basket-item">
            <img
              src="/images/robotman.webp"
              className="basket-item-thumbnail"
              alt="Robot Man NFTs Collection"
            />

            <div className="basket-item-info">
              <h3>Robot Man NFTs Collection</h3>
              <p>NFT collection</p>
            </div>

            <div className="basket-item-qty">
              <button className="qty-button">-</button>
              <span>1</span>
              <button className="qty-button">+</button>
            </div>

            <div className="basket-item-price">£0.00</div>
          </div>
        </div>

        <div className="basket-summary">
          <h2>Order Summary</h2>

          <div className="basket-summary-row">
            <span>Subtotal</span>
            <span>£0.00</span>
          </div>

          <div className="basket-summary-row">
            <span>Tax</span>
            <span>£0.00</span>
          </div>

          <div className="basket-summary-row">
            <span>Discount</span>
            <span>-£0.00</span>
          </div>

          <div className="basket-summary-total">
            <span>Total</span>
            <span>£0.00</span>
          </div>

          <button className="checkout-button">Proceed to Checkout</button>
        </div>
      </div>
    </div>
  );
}

export default App;
