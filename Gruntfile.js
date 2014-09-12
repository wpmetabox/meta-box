module.exports = function ( grunt )
{
	'use strict';

	// Load all Grunt tasks
	require( 'load-grunt-tasks' )( grunt );

	var allJsFiles = ['Gruntfile.js', 'js/*.js', '!js/*.min.js'],
		allCssFiles = 'css/*.css';

	// Grunt configuration
	grunt.initConfig( {

		// Read config file
		pkg         : grunt.file.readJSON( 'package.json' ),

		// Autoprefix CSS
		autoprefixer: {
			options: {
				browsers: [
					'last 2 versions',
					'ie >= 9'
				]
			},
			all    : {
				src: allCssFiles
			}
		},

		// Beautify CSS code
		csscomb     : {
			all: {
				expand: true,
				src   : allCssFiles
			}
		},

		// JSHint
		jshint      : {
			options: {
				reporter: require( 'jshint-stylish' ),
				jshintrc: true // Auto search for .jshintrc files relative to the files being linted
			},
			all    : allJsFiles // Lint all JS files, except *.min.js (libraries)
		}

		// Update translation files
		// makepot: {
		// 	clone: {
		// 		options: {
		// 			cwd: 'deploy/new-themes/<%= pkg.name %>',
		// 			potFilename: '',
		// 			domainPath: '/languages',
		// 			type: 'wp-theme',
		// 			exclude: []
		// 		}
		// 	},
		// 	theme: {
		// 		options: {
		// 			cwd: 'src',
		// 			potFilename: '',
		// 			domainPath: '/languages',
		// 			type: 'wp-theme',
		// 			exclude: []
		// 		}
		// 	}
		// },
	} );


	// Register tasks
	grunt.registerTask( 'default', [
		// CSS tasks
		'autoprefixer',
		'csscomb',

		// JS tasks
		'jshint'
	] );
};
