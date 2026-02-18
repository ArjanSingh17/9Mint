import React, { useEffect, useState } from "react";
import { Link } from "@inertiajs/react";

export default function Homepage() {
    const [theme, setTheme] = useState("dark");

    useEffect(() => {
        // Get theme from localStorage
        const savedTheme = localStorage.getItem("theme") || "dark";
        setTheme(savedTheme);

        // Apply class on <html>
        if (savedTheme === "light") {
            document.documentElement.classList.add("light-mode");
        } else {
            document.documentElement.classList.remove("light-mode");
        }
    }, []);

    // Force background/text to use CSS variables
    const containerStyle = {
        backgroundColor: getComputedStyle(document.documentElement)
            .getPropertyValue("--bg-main"),
        color: getComputedStyle(document.documentElement)
            .getPropertyValue("--text-main"),
        minHeight: "100vh",
        padding: "40px",
        textAlign: "center",
        transition: "background-color 0.3s ease, color 0.3s ease",
    };

    return (
        <div className="homepage-container" style={containerStyle}>
            <h1 className="homepage-title">Welcome to 9Mint Store</h1>

            <p className="homepage-subtitle">
                Your one-stop shop for NFTs, products, or whatever your app sells.
            </p>

            <div className="homepage-buttons">
                <Link href="/login" className="primary-btn">
                    Login
                </Link>

                <Link href="/register" className="secondary-btn">
                    Register
                </Link>
            </div>
        </div>
    );
}

