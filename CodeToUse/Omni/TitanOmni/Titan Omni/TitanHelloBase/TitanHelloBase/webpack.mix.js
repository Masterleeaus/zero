const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public').mergeManifest();

mix.js(__dirname + '/Resources/assets/js/app.js', 'modules/titanhello/js/app.js')
   .js(__dirname + '/Resources/assets/js/inbox.js', 'modules/titanhello/js/inbox.js')
   .sass(__dirname + '/Resources/assets/sass/titanhello.scss', 'modules/titanhello/css/titanhello.css');

if (mix.inProduction()) {
  mix.version();
}
