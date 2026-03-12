import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            manifest: {
                name: 'ITFIP Maps - Navegación Campus',
                short_name: 'ITFIP Maps',
                description: 'Sistema de navegación geoespacial para el campus ITFIP con GPS de alta precisión',
                theme_color: '#00ff88',
                background_color: '#ffffff',
                display: 'standalone',
                start_url: '/',
                scope: '/',
                orientation: 'portrait',
                icons: [
                    {
                        src: '/icon-192x192.svg',
                        sizes: '192x192',
                        type: 'image/svg+xml',
                        purpose: 'any maskable'
                    },
                    {
                        src: '/icon-512x512.svg',
                        sizes: '512x512',
                        type: 'image/svg+xml',
                        purpose: 'any maskable'
                    }
                ]
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,json,vue,txt,woff2}']
            },
            devOptions: {
                enabled: true
            }
        })
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
