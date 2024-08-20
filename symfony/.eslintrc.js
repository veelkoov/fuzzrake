module.exports = {
  extends: [
    "eslint:recommended",
    "plugin:@typescript-eslint/recommended",
    "plugin:vue/vue3-recommended",
  ],
  parser: "vue-eslint-parser",
  parserOptions: {
    parser: "@typescript-eslint/parser",
  },
  plugins: ["@typescript-eslint"],
  root: true,
  rules: {
    "vue/max-attributes-per-line": "off", // FIXME: Optionally. Too much noise for now.
    "vue/no-multi-spaces": [
      "warn",
      {
        ignoreProperties: true, // Allow e.g. alignment for classes
      },
    ],
  },
};
