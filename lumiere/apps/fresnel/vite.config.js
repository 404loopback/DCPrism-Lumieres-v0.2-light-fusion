import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
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
    ],
});
