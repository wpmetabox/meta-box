// Import the original config from the @wordpress/scripts package.
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

// Utilities.
const path = require( 'path' );

// Add any a new entry point by extending the webpack config.
module.exports = {
	...defaultConfig,
	...{
		entry: {
			"block-editor": './js/block-editor/src/index.js',
		},
		output: {
			path: path.resolve( __dirname, 'js/block-editor/build' ),
		},
	},
};
