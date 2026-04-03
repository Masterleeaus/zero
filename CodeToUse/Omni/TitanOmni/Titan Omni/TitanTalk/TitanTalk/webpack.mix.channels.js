const mix = require('laravel-mix');
mix.setPublicPath('../../public').mergeManifest();
mix.js(__dirname + '/Resources/assets/js/widget.js', 'modules/titantalk/js/widget.js');
if (mix.inProduction()) mix.version();
