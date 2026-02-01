import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/layout.css',
                'resources/js/app.js',
                'resources/js/page-scripts/about-us-nft-grid-rotator.js',
                'resources/js/page-scripts/products-collection-preview-rotator.js',
                'resources/js/page-scripts/collections-size-selection.js',
                'resources/js/nft-board/homepage-entry.jsx',
            ],
            refresh: true,
        }),
        react()
        
    ],
});

