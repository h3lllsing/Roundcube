import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    base: process.env.ASSET_URL || '/',
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/permissions.css', 'resources/css/help-center.css', 'resources/js/app.js', 'resources/js/help-center.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/chart.js')) {
                        return 'vendor-chart';
                    }
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
});
