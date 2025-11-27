import React from "react";
import "./pricing.css";
import { Link } from "react-router-dom";

export default function Pricing() {
    return (
        <div>
        <nav className ="navbar">
            <div className="nav-left">
                <img src="/logo.png" alt="Logo" className="logo-image" />
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

    <section id="NFT_collections">
        <h2>Our Collections</h2>

            <div classNamw="Glossy-collection">
                <h3>
                    <Link to="/Glossy-Collection">Glossy Collection</Link>
                </h3>
                <p>This collection contains Glossy Animal NFTs.</p>
                <p>Click to find more about each individual NFT.</p>
            
            <div className="Glossy-Stock">
                <p>Stock: 27</p>
            </div>
        </div>

        <div className="Superhero-collection">
            <h3>
                <Link to="/SuperheroCollection">Superhero Collection</Link>
            </h3>

            <p>This collection contains Superhero NFTs.</p>
            <p>Click to find more about each individual NFT.</p>

            <div className="Superhero-Stock">
                <p>Stock: 35</p>
            </div>
        </div>
    </section>
</div>

  );
}