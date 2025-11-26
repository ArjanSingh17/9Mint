import React from "react";
import { Link } from "@inertiajs/react";

export default function Homepage() {
    return (
        <div style={styles.container}>
            <h1 style={styles.title}>Welcome to 9Mint Store</h1>

            <p style={styles.subtitle}>
                Your one-stop shop for NFTs, products, or whatever your app sells.
            </p>

            <div style={styles.buttons}>
                <Link href="/login" style={styles.button}>
                    Login
                </Link>

                <Link href="/register" style={styles.buttonSecondary}>
                    Register
                </Link>
            </div>
        </div>
    );
}