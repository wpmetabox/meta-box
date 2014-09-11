module.exports = function(grunt) {

	// Load all Grunt tasks via load-grunt-tasks plugin
	require('load-grunt-tasks')(grunt);

	// Grunt configuration
	grunt.initConfig({
		// Read config file
		pkg: grunt.file.readJSON('package.json'),

		// JSHint
		jshint: {
			options: {
				jshintrc: true // Auto search for .jshintrc files relative to the files being linted
			},
			all: ['js/*.js', '!js/*.min.js'] // Lint all JS files, except *.min.js (libraries)
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
	});

	// Default task
	grunt.registerTask('default', [
		'jshint'
	]);
};
