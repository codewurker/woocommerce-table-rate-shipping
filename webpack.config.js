/**
 * The Webpack configuration for WooCommerce Table Rate Shipping.
 *
 * @package WooCommerce_Table_Rate_Shipping
 */

const path          = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const config = {
	...defaultConfig,
	entry: {
		'woocommerce-trs-abort-notices':
			path.resolve(
				process.cwd(),
				'client',
				'abort-notices',
				'index.js'
			),
	},
	output: {
		path: path.resolve( __dirname, 'dist' ),
		filename: '[name].js',
	},
	module: {
		rules: [
			{
				test: /\.(j|t)sx?$/,
				exclude: [ /node_modules/ ],
				loader: 'babel-loader',
		}
		],
	}
};

module.exports = ( env ) => {
	if ( env.mode == 'production' ) {
		config.mode    = 'production';
		config.devtool = false;
	} else {
		config.mode    = 'development';
		config.devtool = 'inline-source-map';
	}
	return config;
};
