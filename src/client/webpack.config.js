const webpack = require("webpack");

module.exports = {
    entry: './src/app/app.js',
    output: {
        filename: './dist/js/atomizer.js'
    },
    module: {
        loaders: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loader: 'babel-loader',
            },
            { test: /\.html$/, loader: "html" },
            { test: /\.css$/, loader: "style!css" },
            { test: /\.less$/, exclude: /node_modules/, loader: "style-loader!css-loader!less-loader" },
            { test: /\.(otf|eot|svg|ttf|woff)/, loader: 'url-loader?limit=8192' }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery'
        })
    ],
    devtool: "#inline-source-map"
};