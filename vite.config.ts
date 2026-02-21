import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import path from 'node:path';

const appUrl = process.env.APP_URL ?? 'https://tdmv.test';
const appHost = new URL(appUrl).hostname;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            detectTls: appHost,
        }),
        vue(),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        hmr: {
            host: appHost,
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
});
