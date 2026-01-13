import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Split large dependencies into separate chunks
                    'video-player': ['video.js'],
                    'vue-vendor': ['vue', 'vue-router', 'pinia'],
                },
            },
        },
        chunkSizeWarningLimit: 600, // Slightly increase limit since splits are in place
    },
});
