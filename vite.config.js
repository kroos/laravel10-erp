import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
// import glob from "glob";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/scss/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '$': 'jquery',
            'swal': 'swal',
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
        },
    },
});
