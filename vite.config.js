import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',   // listen on all interfaces, not just localhost
        hmr: {
            host: 'underwear-unmade-acclimate.ngrok-free.dev', // your ngrok domain
        },
    },
});