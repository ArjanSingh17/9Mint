import React from "react";
import "./contactUs.css";
import { Link } from "react-router-dom";

const Checkout = () => {
  return (
    <div>
       
        {/* NAVBAR */}
      <header className="navbar">
        <div className="logo">
          <img src="/logo.png" alt="Logo" className="logo-image" />
        </div>

        <nav className="nav-links">
        <Link to="/home">Homepage</Link>
         <Link to="/products">Products</Link>
         <Link to="/pricing">Pricing</Link>
         <Link to="/contact">ContactUs</Link>
         
         
        </nav>
      </header> 
  
    <div className="checkout-container">
      <h1>Checkout</h1>

      {/* Shipping Info */}
      <section className="checkout-section">
        <h2>Shipping Information</h2>
        <form>
          <input type="text" placeholder="Full Name" />
          <input type="text" placeholder="Address" />
          <input type="text" placeholder="City" />
          <input type="text" placeholder="Postal Code" />
        </form>
      </section>

      {/* Order Summary */}
      <section className="checkout-section">
        <h2>Your Order</h2>
        <p>No items in cart yet…</p>
        {/* Later: Map through cart items here */}
      </section>

      <button disabled>Proceed to Payment</button>
    </div>
    </div>
  );
};

export default Checkout;
