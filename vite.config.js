import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
// import inject from '@rollup/plugin-inject';
// import path from 'path';
// import glob from "glob";

export default defineConfig({
	plugins: [
		// Add it first
		// inject({
		//     $: 'jquery',
		//     jQuery: 'jquery',
		// }),
		laravel({
			input: [
				'node_modules/jquery/dist/jquery.js',
				'resources/js/app.js',
				// 'node_modules/imagemin/index.js',
				'node_modules/jquery-chained/jquery.chained.js',
				'node_modules/jquery-chained/jquery.chained.remote.js',
				'node_modules/jquery-ui/dist/jquery-ui.js',
				'resources/scss/app.scss',
				'resources/css/app.css',
			],
			refresh: true,
		}),
	],
	css: {
		devSourcemap: true // this one
	},
	// resolve: {
	// 	alias: {
	// 		// '~jquery': path.resolve(__dirname, 'node_modules/jquery/dist/jquery.js'),
	// 		// '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
	// 		// '$': 'jquery',
	// 		// 'jQuery': 'jquery',
	// 		// 'swal': 'swal',
	// 	},
	// },
});
