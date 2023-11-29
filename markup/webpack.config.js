const path = require('path')
const CleanWebpackPlugin = require('clean-webpack-plugin')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const webpack = require('webpack')
const fs = require('fs')


//generates pages html
function generateHtmlPlugins(templateDir) {
    const templateFiles = fs.readdirSync(path.resolve(__dirname, templateDir))
    return templateFiles.map(item => {
        const parts = item.split('.')
        const name = parts[0]
        return new HtmlWebpackPlugin({
            filename: name+'.html',
            template: './assets/page/'+name+'.html'
        })
    })
}

const htmlPlugins = generateHtmlPlugins('./assets/page/')

module.exports = {
    
    entry: './assets/js/common.js',
    
    output: {
        filename: './js/common.bundle.js',
        path: path.resolve(__dirname, 'build/')
    },
    
    module: {
        rules: [
            {
                test: /\.html$/,
                use: [
                  {
                    loader: "html-loader",
                    options: {
                        interpolate: true,
                    }
                  }
                ]
            },
            {
                test: /\.css$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: {
                            publicPath: '../',
                        }
                    },
                  //"css-loader"
                    {
                      loader: 'css-loader',
                      options: {
                        minimize: true,
                      }
                    }
                ]
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                  loader: "babel-loader"
                }
            },
            {
                test: /.(ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
                include: [
                    path.resolve(__dirname, "assets/fonts/")
                ],
                use: {
                  loader: "file-loader",
                  options: {
                    name: "[name]/[name].[ext]",
                    outputPath: 'fonts/',
                  }
                }
            },
            {
                test: /.(png|jpg|ico|gif?)(\?[a-z0-9]+)?$/,
                include: [
                    path.resolve(__dirname, "assets/img/")
                ],
                use: [{
                  loader: "file-loader",
                  options: {
                    name: "[name].[ext]",
                    outputPath: 'img/image/',
                  }
                },{
                    loader: "image-webpack-loader",
                    options: {
                      mozjpeg: {
                        progressive: true,
                        quality: 75,
                      },
                      // optipng.enabled: false will disable optipng
                      optipng: {
                        enabled: false
                      },
                      pngquant: {
                        quality: "75",
                        speed: 4
                      },
                      gifsicle: {
                        interlaced: true
                      },
                    }
                }]
            },
            {
                test: /.(svg)(\?[a-z0-9]+)?$/,
                include: [
                    path.resolve(__dirname, "assets/img/")
                ],
                use: [
                {
                    loader: "file-loader",
                    options: {
                        name: "[name].[ext]",
                        outputPath: "img/icon/",
                    }
                }, 
                {
                    loader: "image-webpack-loader",
                }]
            },
            
        ]
    }, 
    
    resolve: {
      alias: {
        //"TweenLite": path.resolve('node_modules', 'gsap/src/uncompressed/TweenLite.js'),
        //"TweenMax": path.resolve('node_modules', 'gsap/src/uncompressed/TweenMax.js'),
        //"TimelineLite": path.resolve('node_modules', 'gsap/src/uncompressed/TimelineLite.js'),
        //"TimelineMax": path.resolve('node_modules', 'gsap/src/uncompressed/TimelineMax.js'),
        //"ScrollMagic": path.resolve('node_modules', 'scrollmagic/scrollmagic/uncompressed/ScrollMagic.js'),
        //"animation.gsap": path.resolve('node_modules', 'scrollmagic/scrollmagic/uncompressed/plugins/animation.gsap.js'),
        //"debug.addIndicators": path.resolve('node_modules', 'scrollmagic/scrollmagic/uncompressed/plugins/debug.addIndicators.js'),
        //"ScrollToPlugin": path.resolve('node_modules', 'gsap/ScrollToPlugin.js'),
      }
    },
    
    plugins: [
        new CleanWebpackPlugin(['build']),
        new MiniCssExtractPlugin({
            filename: "./css/[name].css",
            chunkFilename: "[id].css"
        }),
        
        new webpack.ProvidePlugin({
            //plugins
        })
       
    ].concat(htmlPlugins),
    
    devServer: {
        host: process.env.HOST || 'localhost',
        contentBase: path.join(__dirname, 'build'),
        compress: true,
        port: 9000,
        allowedHosts: [
            '.creonit.ru',
            'localhost'
        ]
    },
    
};