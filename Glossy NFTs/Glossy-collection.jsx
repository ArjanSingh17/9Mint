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

    <div className="Duck-NFT">
        <img src="/GlossyDuckNFT.png" alt= "Duck" className="nft-image" />

        <div className="nft-info">
            <h2>Glossy Duck</h2>
            <p className="nf-description">
                This NFT is a glossy portrait of a duck.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after watching a duck waddle along.</p>

            <div className="size-option">
                <p>Select your size:</p>
                <button className="small-size">Small</button>
                <button className="medium-size">medium</button>
                <button className="large-size">large</button>
            </div>

            <button className="Add-to-basket">Add to basket</button>
        </div>
    </div>

    <div className="Cat-NFT">
        <img src="/GlossyCat.png" alt= "Cat" className="nft-image" />

        <div className="nft-info">
            <h2>Glossy Cat</h2>
            <p className="nf-description">
                This NFT is a glossy portrait of a cat.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after witnessing a kitten following its mother.</p>

            <div className="size-option">
                <p>Select your size:</p>
                <button className="small-size">Small</button>
                <button className="medium-size">medium</button>
                <button className="large-size">large</button>
            </div>

            <button className="Add-to-basket">Add to basket</button>
        </div>
    </div>

    <div className="Donkey-NFT">
        <img src="/GlossyDonkeyNFT.png" alt= "Donkey" className="nft-image" />

        <div className="nft-info">
            <h2>Glossy Donkey</h2>
            <p className="nf-description">
                This NFT is a glossy portrait of a Donkey.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after watching Shrek.</p>

            <div className="size-option">
                <p>Select your size:</p>
                <button className="small-size">Small</button>
                <button className="medium-size">medium</button>
                <button className="large-size">large</button>
            </div>

            <button className="Add-to-basket">Add to basket</button>
        </div>
    </div>

    <div className="Giraffe-NFT">
        <img src="/GlossyGiraffeNFT.png" alt= "Giraffe" className="nft-image" />

        <div className="nft-info">
            <h2>Glossy Giraffe</h2>
            <p className="nf-description">
                This NFT is a glossy portrait of a giraffe.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after watching Madagascar.</p>

            <div className="size-option">
                <p>Select your size:</p>
                <button className="small-size">Small</button>
                <button className="medium-size">medium</button>
                <button className="large-size">large</button>
            </div>

            <button className="Add-to-basket">Add to basket</button>
        </div>
    </div>

    <div className="Lobster-NFT">
        <img src="/GlossyLobsterNFT.png" alt= "Lobster" className="nft-image" />

        <div className="nft-info">
            <h2>Glossy Lobster</h2>
            <p className="nf-description">
                This NFT is a glossy portrait of a lobster.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after seeing pictures on pinterest.</p>

            <div className="size-option">
                <p>Select your size:</p>
                <button className="small-size">Small</button>
                <button className="medium-size">medium</button>
                <button className="large-size">large</button>
            </div>

            <button className="Add-to-basket">Add to basket</button>
        </div>
    </div>

    <div className="Rooster-NFT">
        <img src="/GlossyRoosterNFT.png" alt= "Rooster" className="nft-image" />

        <div className="nft-info">
            <h2>Glossy Rooster</h2>
            <p className="nf-description">
                This NFT is a glossy portrait of a rooster.This was created by our highly skilled artist Vlas.</p>
                    <p> The inspiration for this piece is unknown.</p>

            <div className="size-option">
                <p>Select your size:</p>
                <button className="small-size">Small</button>
                <button className="medium-size">medium</button>
                <button className="large-size">large</button>
            </div>

            <button className="Add-to-basket">Add to basket</button>
        </div>
    </div>

    <div className="Squirrel-NFT">
        <img src="/GlossySquirrelNFT.png" alt= "Squirrel" className="nft-image" />

        <div className="nft-info">
            <h2>Glossy Squirrel</h2>
            <p className="nf-description">
                This NFT is a glossy portrait of a sqirrel.This was created by our highly skilled artist Vlas.</p>
                    <p> Our artist, Vlas, designed this after being inspired by the squirrels on campus.</p>

            <div className="size-option">
                <p>Select your size:</p>
                <button className="small-size">Small</button>
                <button className="medium-size">medium</button>
                <button className="large-size">large</button>
            </div>

            <button className="Add-to-basket">Add to basket</button>
        </div>
    </div>
</div>

    );
}