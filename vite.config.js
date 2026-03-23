import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
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
            tailwindcss(),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
        server: {
            host: '0.0.0.0',
            port: parseInt(env.VITE_DEV_SERVER_PORT || '5173'),
            hmr: {
                host: 'localhost',
            },
            watch: {
                usePolling: true,
            },
        },
    };
});
