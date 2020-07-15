const { src, dest, series } = require('gulp');
const babel = require('gulp-babel');
const concat = require('gulp-concat');
const terser = require('gulp-terser');
const clean = require('gulp-clean');

const validationCompile = () => {
	return src('js/validation/validation.js')
		.pipe(babel({
			presets: ['@babel/env']
		}))
		.pipe(dest('js/build/'));
}

const validationConcat = () => {
	return src(['js/validation/jquery.validate.js', 'js/validation/additional-methods.js', 'js/build/validation.js'])
		.pipe(concat('validation.min.js'))
		.pipe(terser())
		.pipe(dest('js'));
}

const validationClean = () => {
	return src('js/build', {read: false})
		.pipe(clean());
}

exports.default = series( validationCompile, validationConcat, validationClean );