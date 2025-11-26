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
            
    <nav className="nav-links">
        <Link to="/home">Homepage</Link>
        <Link to="/AboutUs">AbouttUs</Link>
         <Link to="/products">Products</Link>
         <Link to="/pricing">Pricing</Link>
         <Link to="/contact">ContactUs</Link>
         
        </nav>

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
