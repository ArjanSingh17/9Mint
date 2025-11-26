import React from "react";
import "./contactUs.css"; // make sure this file is in the same folder or update the path

export default function ContactUs() {
  return (
    <div>
      {/* Navbar */}
      <header className="navbar">
        <div className="logo">
          <img src="/logo.png" alt="Logo" className="logo-image" />
        </div>

        <nav className="nav-links">
          <a href="/products">Products</a>
          <a href="/home">Home</a>
          <a href="/products">Products</a>
          <a href="/pricing">Pricing</a>
          <a href="/aboutus">About Us</a>
        </nav>

        <div className="authentication-buttons">
       
          <button className="account">Account</button>
        </div>
      </header>

      {/* Contact Form */}
      <main className="contactUs-section">
        <h2>Contact Us</h2>

        <form className="contactUs-form">
          <label htmlFor="name">Name:</label>
          <input type="text" name="name" placeholder="Name" required />

          <label htmlFor="email">Email:</label>
          <input type="email" name="email" placeholder="Email" required />

          <label htmlFor="message">Message:</label>
          <textarea
            id="message"
            name="message"
            rows="5"
            placeholder="Message"
            required
          ></textarea>

          <button type="submit">Submit</button>
        </form>
      </main>

      {/* Footer */}
      <footer className="footer">
        <div className="footer-links">
          <a href="/terms">Terms & Conditions</a>
          <a href="/faqs">FAQs</a>
        </div>
        <p>&copy; 2025 Your Company. All rights reserved.</p>
      </footer>
    </div>
  );
}
