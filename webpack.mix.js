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
    .sass('resources/sass/app.scss', 'public/css');
    

let packages = 'node_modules/';
let gentelella = packages+'gentelella/';
let vendors = gentelella+'vendors/';


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

// mix.copy(vendors+'font-awesome/fonts/','public/fonts/');
// mix.copy(gentelella+'build/js/custom.js','resources/assets/js/');



mix.combine([
    vendors+'bootstrap/dist/css/bootstrap.css',
    vendors+'font-awesome/css/font-awesome.css',
    vendors+'pnotify/dist/pnotify.css',
    vendors+'select2/dist/css/select2.css',
    vendors+'nprogress/nprogress.css',
    vendors+'bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min',
    //Now lets add the custom css
    gentelella+'build/css/custom.css',//this is the default from gentelella
    'resources/assets/css/mycustom.css' //All your custom css can go here

],'public/css/gapp.css');

mix.combine([
    vendors+'/jquery/dist/jquery.js',
    vendors+'bootstrap/dist/js/bootstrap.min.js',
    vendors+'pnotify/dist/pnotify.js',
    vendors+'bootstrap-progressbar/bootstrap-progressbar.min.js',
    vendors+'fastclick/lib/fastclick.js',
    vendors+'select2/dist/js/select2.js',
    vendors+'nprogress/nprogress.js',
    vendors+'icheck/icheck.js',
    //lets add custom scripts
    'resources/assets/js/custom.js',//this default from gentelella
    'resources/assets/js/init.js' //initialize other plugins here
],'public/js/gapp.js');