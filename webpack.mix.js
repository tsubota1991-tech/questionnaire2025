// webpack.mix.js
const mix = require('laravel-mix');

mix
  .js('resources/js/app.js', 'public/js')
  .js('resources/js/admin/questions-create.js', 'public/js/admin')
  // PostCSS のプラグインは postcss.config.js を使うので第3引数は渡さない
  .postCss('resources/css/app.css', 'public/css')
  .version();

// if (!mix.inProduction()) mix.sourceMaps();
