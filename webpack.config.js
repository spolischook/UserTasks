const path = require('path'),
    ExtractTextPlugin = require('extract-text-webpack-plugin'),
    webpack = require('webpack'),
    cssLoader = {
        loader: 'css-loader',
        options: {
            sourceMap: true,
            minimize: true,
        }
    },
    sassLoader = {
        loader: 'sass-loader',
        options: {
            sourceMap: true
        }
    },
    styleLoader = {
        loader: 'style-loader',
        options: {
            sourceMap: true
        }
    },
    resolveUrlLoader = {
        loader: 'resolve-url-loader',
        options: {
            sourceMap: true
        }
    },
    fileLoader = {
        loader: "file-loader",
        options: {
            name: '[name]-[hash:6].[ext]'
        }
    };

module.exports = {
    entry: {
        main: './src/Resources/main.js',
        taskFormPreview: './src/Resources/js/taskFormPreview.js'
    },
    output: {
        path: path.resolve(__dirname, 'public', 'build'),
        filename: '[name].js'
    },
    plugins: [
        new webpack.ProvidePlugin({
            jQuery: 'jquery',
            $: 'jquery',
            'window.jQuery': 'jquery',
        }),
        new ExtractTextPlugin(
            '[name].css'
        ),
    ],
    module: {
        rules: [
            {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract({
                    use: [
                        cssLoader,
                        resolveUrlLoader,
                        sassLoader,
                    ],
                    fallback: styleLoader,
                })

            },
            {
                test: /\.(png|jpg|jpeg|gif|ico|svg)$/,
                use: fileLoader
            },
            {
                test: /\.(woff|woff2|eot|ttf|otf)$/,
                use: fileLoader
            }
        ]
    }
};
