module.exports = {
    extends: [
        'eslint:recommended',
        'plugin:@typescript-eslint/recommended',
        'plugin:vue/vue3-essential', // TODO: https://github.com/veelkoov/fuzzrake/pull/187/files Upgrade ---> strongly-recommended ---> recommended
    ],
    'parser': 'vue-eslint-parser',
    'parserOptions': {
        'parser': '@typescript-eslint/parser',
    },
    plugins: ['@typescript-eslint'],
    root: true,

};
