import mix from 'laravel-mix';


mix.js('resources/js/admin.js', 'public/js')
   .sass('resources/sass/admin.scss', 'public/css')
   .combine([
       'public/vendor/adminlte/dist/css/adminlte.min.css',
       'public/vendor/fontawesome-free/css/all.min.css',
       'public/css/modern-admin.css'
   ], 'public/css/admin-bundle.css')
   .combine([
       'public/vendor/jquery/jquery.min.js',
       'public/vendor/bootstrap/js/bootstrap.bundle.min.js',
       'public/vendor/adminlte/dist/js/adminlte.min.js',
       'public/js/modern-admin.js'
   ], 'public/js/admin-bundle.js')
   .version();
   mix.webpackConfig({
   resolve: {
    extensions: ['*', '.wasm', '.mjs', '.js', '.jsx', '.json']
}
});