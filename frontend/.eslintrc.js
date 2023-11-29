module.exports = {
    root: true,
    env: {
        browser: true,
        node: true
    },
    parserOptions: {
        parser: 'babel-eslint'
    },
    extends: [
        'plugin:vue/recommended',
        'eslint:recommended'
    ],
    plugins: [],
    rules: {
        'linebreak-style': ['error', 'unix'],
        indent: ['error', 4],
        semi: ['error', 'always'],
        'comma-dangle': ['error', 'never'],
        'comma-spacing': ['error', {
            after: true
        }],
        quotes: ['error', 'single'],
        'vue/html-indent': ['error', 4],
        'vue/max-attributes-per-line': ['error', {
            singleline: 5
        }],
        'vue/no-template-shadow': 'off',
        'vue/no-v-html': 'off',
        'vue/require-default-prop': 'off',
        'vue/html-self-closing': ['error', {
            html: {
                void: 'never',
                normal: 'never',
                component: 'always'
            },
            svg: 'always'
        }],
        'padded-blocks': 'off',
        'keyword-spacing': ['error', {
            after: true,
            before: true
        }],
        'arrow-spacing': ['error', {
            after: true,
            before: true
        }],
        'space-before-blocks': ['error', 'always'],
        'brace-style': ['error', '1tbs', { allowSingleLine: true }],
        'space-in-parens': ['error', 'never'],
        'quote-props': ['error', 'as-needed'],
        'key-spacing': ['error'],
        'template-curly-spacing': ['error', 'always'],
        'space-before-function-paren': ['error', 'never'],
        'no-unused-vars': ['warn'],
        'space-infix-ops': ['error'],
        'eol-last': ['error', 'always'],
        'no-whitespace-before-property': ['error'],
        'newline-per-chained-call': ['error', { ignoreChainWithDepth: 2 }],
        'padding-line-between-statements': ['error',  { blankLine: 'always', prev: '*', next: ['return'] }],
        'no-multiple-empty-lines': ['error', { max: 2, maxBOF: 1 }],
        'array-bracket-spacing': ['error', 'never'],
        'object-curly-spacing': ['error', 'always'],
        'block-spacing': ['error'],
        'computed-property-spacing': ['error', 'never'],
        'func-call-spacing': ['error', 'never']
    }
};
