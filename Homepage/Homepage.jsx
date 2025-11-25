import React from "react";
import "./styles.css";
import { Link } from "react-router-dom";

export default function Homepage() {
    return (
        <div>
        <nav className ="navbar">
            <div className="nav-left">
                <img src="/logo.png" alt="Logo" className="logo-image">
        </div>
            
    <ul className="nav-center">
      <Route path="/homepage" element={<Homepage />} />
      <Route path="/aboutus" element={<Aboutus />} />
      <Route path="/products" element={<Products />} />
      <Route path="/pricing" element={<Pricing />} />
      <Route path="/contactus" element={<ContactUs />} />
      </ul>

      <div className="nav-right">
        <Link to="/account" className="account-link">Account</Link>
      </div>
    </nav>

    <section id="Information" className="Org">
        <div className="image-left">
            <img sec="/NFT1.png" alt="Left NFT"/>
        </div>

        <div className="Informationstyle">
            <h2>Need an NFT?</h2>
            <h3>We got you!</h3>
        </div>
    <div className="image-right">
            <img sec="/NFT4.png" alt="right NFT"/>
        </div>
    </section>
    </div>
   );
}