'use strict';

module.exports = function ( grunt )
{
	// Load all Grunt tasks
	require( 'load-grunt-tasks' )( grunt );

	// Grunt configuration
	grunt.initConfig( {
		// Read config file
		pkg         : grunt.file.readJSON( 'package.json' ),

		// JSHint
		jshint      : {
			options: {
				jshintrc: true // Auto search for .jshintrc files relative to the files being linted
			},
			all    : ['js/*.js', '!js/*.min.js'] // Lint all JS files, except *.min.js (libraries)
		},

		// CSS tasks

		// Autoprefix
		autoprefixer: {
			options: {
				browsers: [
					'last 2 versions',
					'ie >= 9'
				]
			},
			all    : {
				src: 'css/*.css'
			}
		},

		// CSSComb: beautify CSS code
		csscomb: {
			all: {
				expand: true,
				src   : 'css/*.css'
			}
		},

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


	// Build task
	grunt.registerTask( 'build', [
		'build:css'
		//'build:js',
		//'build:i18n',
		//'imagemin'
	] );

	grunt.registerTask( 'build:css', [
		'autoprefixer',
		'csscomb'
	] );

	// Default task
	grunt.registerTask( 'default', [
		'build'
	] );
};
