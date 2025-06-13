const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin')
const cssnano = require('cssnano') // https://cssnano.co/
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')
const BrowserSyncPlugin = require('browser-sync-webpack-plugin')
const nameProject = 'templatecore'
const JS_DIR = path.resolve(__dirname, 'src')
const BUILD_DIR = path.resolve(__dirname, 'build')

const entry = {
    main: JS_DIR + '/index.js',
}

const output = {
    path: BUILD_DIR,
    filename: 'js/[name].js',
}
const plugins = (argv) => [
    new CleanWebpackPlugin({
        cleanStaleWebpackAssets: argv.mode === 'production', // Automatically remove all unused webpack assets on rebuild, when set to true in production. ( https://www.npmjs.com/package/clean-webpack-plugin#options-and-defaults-optional )
    }),

    new MiniCssExtractPlugin({
        filename: 'css/[name].css',
    }),
    new BrowserSyncPlugin({
        host: 'localhost',
        port: 3000,
        proxy: `http://${nameProject}.local/`,
        files: ['**/*.'],
    }, {
        reload: true,
    }),
]

const rules = [{
        test: /\.js$/,
        include: [JS_DIR],
        exclude: /node_modules/,
        use: 'babel-loader',
    },
    {
        test: /\.scss$/,
        exclude: /node_modules/,
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
    },
    {
        test: /\.(png|jpg|svg|jpeg|gif|ico)$/,
        use: {
            loader: 'file-loader',
            options: {
                name: '[path][name].[ext]',
                publicPath: 'production' === process.env.NODE_ENV ? '../' : '../../',
            },
        },
    },
]
module.exports = (env, argv) => ({
    entry: entry,
    output: output,
    devtool: 'source-map',
    plugins: plugins(argv),
    module: {
        rules: rules,
    },
    optimization: {
        minimizer: [
            new OptimizeCssAssetsPlugin({
                cssProcessor: cssnano,
            }),
            new UglifyJsPlugin({
                cache: false,
                parallel: true,
                sourceMap: false,
            }),
        ],
    },
    externals: {
        jquery: 'jQuery',
    },
})