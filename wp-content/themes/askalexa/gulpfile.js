// Include gulp
var gulp = require('gulp');
var browserSync = require('browser-sync').create();

// Include Our Plugins
var jshint = require('gulp-jshint');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');

var config = {
	bowerDir: './assets/vendor', 
	npmDir: './node_modules'
}

// Dynamic Server + watching scss/html files
gulp.task('serve', ['sass'], function() {
	browserSync.init({
		proxy: 'http://funthingstoaskalexa.dev',
		host: 'localhost',
		open: 'external',
		target: 'funthingstoaskalexa.dev'
	});
	 
	gulp.watch("assets/styles/**/*.scss", ['sass'] );
	gulp.watch("assets/scripts/**/*.js", ['scripts'] ).on('change', browserSync.reload);
	gulp.watch("templates/*.twig").on('change', browserSync.reload) ;
});


// Lint Task
gulp.task('lint', function() {
	return gulp.src('js/*.js')
		.pipe(jshint())
		.pipe(jshint.reporter('default'));
});

// Compile Our Sass
gulp.task('sass', function() {
	return gulp.src('assets/styles/*.scss')
	.pipe(sass({
		outputStyle: 'compressed',
		includePaths: [
			config.bowerDir + '/reset-scss',
			config.bowerDir + '/slick-carousel/slick'
			// config.bowerDir + '/bourbon/app/assets/stylesheets',
			// config.bowerDir + '/neat/core' 
		]
	}) )
	// .pipe(gulp.dest('dist/css'));
	.pipe(gulp.dest(''))
	.pipe(browserSync.stream());
});

// Concatenate & Minify JS
gulp.task('scripts', function() {
	return gulp.src('js/*.js')
		.pipe(concat('all.js'))
		.pipe(gulp.dest('dist'))
		.pipe(rename('all.min.js'))
		.pipe(uglify())
		.pipe(gulp.dest('dist/js'));
});

// Watch Files For Changes
gulp.task('watch', function() {
	gulp.watch('js/*.js', ['lint', 'scripts']);
	gulp.watch('assets/styles/**/*.scss', ['sass']);
});

// Default Task
gulp.task('default', ['serve', 'lint', 'sass', 'scripts', 'watch']);