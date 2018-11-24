var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/assets/')
    .setPublicPath('/assets')
    .addEntry('general', './assets/js/general.js')
    .addEntry('main', './assets/js/main/main.js')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableTypeScriptLoader()
    .enableLessLoader()
;

module.exports = Encore.getWebpackConfig();
module.exports['externals'] = {
    jquery: 'jQuery'
};
