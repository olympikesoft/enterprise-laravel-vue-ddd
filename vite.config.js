import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path'; // Import path module

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'), // Alias for resources/js
            // You can add more aliases if needed
            // 'vue': 'vue/dist/vue.esm-bundler.js', // If you encounter issues with Vue runtime vs compiler
        },
    },
    // Optional: Server configuration if you run Vite dev server separately
    // server: {
    //     hmr: {
    //         host: 'localhost',
    //     },
    //     // proxy: { // If you need to proxy API requests from Vite dev server to Laravel backend
    //     //   '/api': {
    //     //     target: 'http://localhost:8000', // Your Laravel backend
    //     //     changeOrigin: true,
    //     //     // rewrite: (path) => path.replace(/^\/api/, '') // if your laravel routes don't have /api prefix
    //     //   },
    //     //   '/sanctum': { // For CSRF cookie
    //     //     target: 'http://localhost:8000',
    //     //     changeOrigin: true,
    //     //   }
    //     // }
    // }
});
