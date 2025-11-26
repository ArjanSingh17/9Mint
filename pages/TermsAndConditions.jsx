import React from "react";
import { Link } from "react-router-dom";
import "./contactUs.css"; // same stylesheet you were using in HTML

const TermsAndConditions = () => {
  return (
    <div>
      <header className="navbar">
        <div className="logo">
          <img src="/logo.png" alt="Logo" className="logo-image" />
        </div>

        <nav className="nav-links">
          <Link to="/products">Products</Link>
          <Link to="/home">Home</Link>
          <Link to="/pricing">Pricing</Link>
          <Link to="/account">Account</Link>
          <Link to="/aboutus">About Us</Link>
        </nav>
      </header>

      <main className="terms-section">
        <h2>Terms and Conditions</h2>
        <p>
          Welcome to Our Website. By accessing or using our website, you agree
          to comply with and be bound by the following terms and conditions.
        </p>

        <h3>1. Use of Website</h3>
        <p>
          You agree to use the website only for lawful purposes and in a way
          that does not infringe the rights of others.
        </p>

        <h3>2. Intellectual Property</h3>
        <p>
          All content on this website is the property of Our Company and
          protected by law.
        </p>

        <h3>3. Limitation of Liability</h3>
        <p>
          We are not liable for damages arising from your use of the website.
        </p>

        <h3>4. Changes to Terms</h3>
        <p>
          We may update these terms at any time. Continued use means you accept
          the new terms.
        </p>

        <h3>5. Governing Law</h3>
        <p>
          These terms follow the laws of your jurisdiction and you agree to the
          authority of those courts.
        </p>

        <p>
          If you have more questions, click{" "}
          <Link to="/faqs" className="FAQs">
            here
          </Link>{" "}
          to view the FAQs.
        </p>
      </main>
    </div>
  );
};

export default TermsAndConditions;
