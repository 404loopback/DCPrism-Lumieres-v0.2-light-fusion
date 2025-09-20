import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    server: {
        port: 5174,
        host: '127.0.0.1',
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/filament/theme.css', // Nouveau thème global (ex-manager)
                'resources/css/needy/needy.css' // Needy button styles
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
