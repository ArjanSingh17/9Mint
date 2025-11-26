    

import React from "react";
import "./contactUs.css";
import { Link } from "react-router-dom";

export default function AboutUs() {
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

         <div className="authentication-buttons">
       
          <button className="account">Account</button>
        </div>
      </header>

      {/* GROUP NAME */}
      <section className="Groupname">
        <h1>9 MINT</h1>
        <p>All about art and creativity</p>
      </section>

      {/* NFT GRID */}
      <section className="nft-grid">
        <img src="/nft1.png" alt="" />
        <img src="/nft2.png" alt="" />
        <img src="/nft3.png" alt="" />
        <img src="/nft4.png" alt="" />
      </section>

      {/* ABOUT SECTION */}
      <section className="about-section">
        <h2>Who Are We?</h2>
        <p>
          9Mint is a simulated e-commerce platform designed to sell and manage
          Non-Fungible Tokens (NFTs). At 9 MINT, our mission is to foster a
          vibrant community of art enthusiasts and creators.
        </p>

        <h2>Our Journey</h2>
        <p>
          Founded in 2023, 9 MINT began as a small group of artists and tech
          enthusiasts passionate about digital art and NFTs.
        </p>

        <h2>Our Community</h2>
        <p>
          We believe art is for everyone. Our platform connects artists and
          collectors worldwide.
        </p>
      </section>

      {/* TEAM SECTION */}
      <section className="team-section">
        <h2>Meet the Team</h2>

        <div className="team-grid">
          {[
            { name: "Naomi", role: "Team mediator and Front end engineer" },
            { name: "Arjan", role: "Team Leader and Back end engineer" },
            { name: "Maliyka", role: "Front end engineer leader" },
            { name: "Kalil", role: "Backed developer and Proofreader" },
            { name: "Dariusz", role: "Backed engineer and project " },
            { name: "Hamza", role: "Team creative and frontend developer" },
            { name: "Jahirul", role: "Backend Engineer and Timekeeper" },
            { name: "Vlas", role: "Front/Backen Engineer and digital artist" },
          ].map((member, index) => (
            <div className="team-card" key={index}>
              <img src="/logo.png" alt={member.name} />
              <h3>{member.name}</h3>
              <p>Role: {member.role}</p>
            </div>
          ))}
        </div>
      </section>

      {/* CONTACT LINK */}
      <section className="aboutus-section">
        <h2>Get in Touch</h2>
        <p>We love connecting with art lovers, creators, and collectors.</p>
        <p>
          If you'd like to reach us directly, click{" "}
          <a href="/contact" className="contact-link">
            here
          </a>{" "}
          to contact us.
        </p>
      </section>

    </div>
  );
}
