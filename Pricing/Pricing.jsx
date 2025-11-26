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

    <section className="Pricing-info">
        <p>
          Here at 9Mint, we value the hard work and creativity put in by our NFT designers. Therefore, when pricing, we try to</p>
        <p>
          make them as affordable as possible whilst also, doing justice to the designer.
        </p>
      </section>

     <main>
        <section id="Pricing_sizes" className="Org">
            <h3>In general:</h3>
            
            <div className="pricing-grid">
                <div className="pricing-item pricing-small">
                    <div className="pricing-content">
                    <div className="diagram">
                        <img src="/NFT-small.png" alt="Small diagram" />
                    </div>
                <div className="pricing-text">
                    <h4>Small:</h4>
                    <p>Ranges from £45 to £70</p>
                    </div>
                </div>
            </div>

            <div className="pricing-item pricing-medium">
                <div className="pricing-content">
                    <div className="diagram">
                        <img src="/NFT-medium.png" alt="Medium diagram" />
                    </div>
                <div className="pricing-text">
                    <h4>Medium:</h4>
                    <p>Ranges from £70 to £200</p>
                    </div>
                </div>
            </div>

            <div className="pricing-item pricing-large">
                <div className="pricing-content">
                    <div className="diagram">
                        <img src="/NFT-large.png" alt="Large diagram" />
                    </div>
                <div className="pricing-text">
                    <h4>Large:</h4>
                    <p>Ranges from £250 to £500</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main> 
</div>
);
}