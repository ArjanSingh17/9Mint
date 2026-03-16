import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: '10.210.227.4',
            port: 5173,
            protocol: 'ws',
        }
    },
    plugins: [
        tailwindcss(),
        laravel({
          input: [
        'resources/css/theme-tokens.css',
        'resources/css/theme-components.css',
        'resources/css/theme-layer.css',
        'resources/css/app.css',
        'resources/css/layout.css',
        'resources/css/nft-board.css',
        'resources/css/pages/about-contact.css',
        'resources/css/pages/app-pages.css',
        'resources/css/pages/chat.css',
        'resources/css/pages/checkout.css',
        'resources/css/pages/collections-legacy.css',
        'resources/css/pages/pricing.css',
        'resources/css/pages/products.css',
        'resources/css/pages/trending.css',
        'resources/js/app.js',
        'resources/js/page-scripts/about-us-nft-grid-rotator.js',
        'resources/js/page-scripts/about-us-reviews-slider.js',
        'resources/js/page-scripts/products-collection-preview-rotator.js',
        'resources/js/page-scripts/quote-refresh.js',
        'resources/js/page-scripts/checkout-expiry.js',
        'resources/js/page-scripts/checkout-payment.js',
        'resources/js/nft-marketplace/marketplace-entry.jsx',
        'resources/js/nft-board/homepage-entry.jsx',
    ],
            refresh: true,
        }),
        react()
    ],
});