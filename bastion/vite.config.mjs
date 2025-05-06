import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { VitePWA } from 'vite-plugin-pwa'

const isDevEnvironment = 'dev' === process.env.NODE_ENV || true;

export default defineConfig({
    plugins: [
        react(),
        laravel({
            input: [
                'resources/js/app.jsx',
                'resources/css/app.css',
            ],
            refresh: true,
        }),
        VitePWA({
            strategies: 'injectManifest',
            srcDir: 'resources/js',
            includeAssets: ['assets/app-icon.webp'],
            manifest: {
                name: 'abenevaut.dev',
                short_name: 'abenevaut',
                description: 'Application Management',
                theme_color: '#f4f4f5',
                icons: [
                    {
                        src: 'https://www.abenevaut.dev/images/abenevaut-app-icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: 'https://www.abenevaut.dev/images/abenevaut-app-icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    }
                ]
            }
        }),
    ],
});
