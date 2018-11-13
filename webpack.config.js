var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/assets/')
    .setPublicPath('/assets')
    .addEntry('general', './assets/js/general.js')
    .addEntry('main', './assets/js/main.js')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableLessLoader()
;

module.exports = Encore.getWebpackConfig();
module.exports['externals'] = {
    jquery: 'jQuery'
};
