var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/assets/')
    .setPublicPath('/assets')
    .addEntry('general', './assets/js/entry/general.ts')
    .addEntry('main', './assets/js/entry/main.ts')
    .addEntry('events', './assets/js/entry/events.ts')
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

module.exports.output.library = 'fuzzrake';
module.exports.output.libraryTarget = 'window';
