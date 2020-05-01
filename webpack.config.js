var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/assets/')
    .setPublicPath('/assets')
    .addEntry('general', './assets/scripts/entry/general.ts')
    .addEntry('main', './assets/scripts/entry/main.ts')
    .addEntry('events', './assets/scripts/entry/events.ts')
    .addEntry('info', './assets/scripts/entry/info.ts')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enableTypeScriptLoader((config) => {
        config.configFile = '../tsconfig.json'; // Relative to the entrypoint
    })
    .enableLessLoader()
;

module.exports = Encore.getWebpackConfig();
module.exports['externals'] = {
    jquery: 'jQuery'
};

module.exports.output.library = 'fuzzrake';
module.exports.output.libraryTarget = 'window';
