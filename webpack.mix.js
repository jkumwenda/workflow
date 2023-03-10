const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .version();

mix.copy('node_modules/signature_pad/dist/signature_pad.umd.min.js', 'public/js/signature_pad.min.js');

mix.version(['public/css/custom.css', 'public/css/rplus.css', 'public/js/rplus.js']);