var gulp = require('gulp');

var config = require('../../config.js');

// Dependencies
var browserSync = require('browser-sync');
var concat      = require('gulp-concat');
var del         = require('del');
var fs          = require('fs');
var less        = require('gulp-less');
var minifyCSS   = require('gulp-minify-css');
var rename      = require('gulp-rename');
var symlink     = require('gulp-symlink');
var sass        = require('gulp-ruby-sass');
var uglify      = require('gulp-uglify');
var zip         = require('gulp-zip');

var baseTask  = 'components.redeventb2b';
var extPath   = '../redeventb2b';
var mediaPath = extPath + '/media/com_redeventb2b';

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':frontend',
		'clean:' + baseTask + ':backend',
		'clean:' + baseTask + ':media'
	],
	function() {
		return true;
});

// Clean: frontend
gulp.task('clean:' + baseTask + ':frontend', function() {
	del.sync(config.wwwDir + '/components/com_redeventb2b', {force : true});
});

// Clean: backend
gulp.task('clean:' + baseTask + ':backend', function() {
	del.sync(config.wwwDir + '/administrator/components/com_redeventb2b', {force : true});
});

// Clean: media
gulp.task('clean:' + baseTask + ':media', function() {
	del.sync(config.wwwDir + '/media/com_redeventb2b', {force : true});
});

// Copy
gulp.task('copy:' + baseTask,
	[
		'copy:' + baseTask + ':frontend',
		'copy:' + baseTask + ':backend',
		'copy:' + baseTask + ':media'
	],
	function() {
		return true;
});

// Copy: frontend
gulp.task('copy:' + baseTask + ':frontend', ['clean:' + baseTask + ':frontend'], function() {
	return gulp.src(extPath + '/site/**')
		.pipe(gulp.dest(config.wwwDir + '/components/com_redeventb2b'));
});

// Copy: backend
gulp.task('copy:' + baseTask + ':backend', ['clean:' + baseTask + ':backend'], function(cb) {
	return (
		gulp.src([
			extPath + '/admin/**'
		])
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redeventb2b')) &&
		gulp.src(extPath + '/redeventb2b.xml')
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redeventb2b')) &&
		gulp.src(extPath + '/install.php')
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redeventb2b'))
	);
});

// Copy: media
gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
	return gulp.src(mediaPath + '/**')
		.pipe(gulp.dest(config.wwwDir + '/media/com_redeventb2b'));
});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':frontend',
		'watch:' + baseTask + ':backend',
		'watch:' + baseTask + ':media'
		//'watch:' + baseTask + ':less'
	],
	function() {
		return true;
});

// Watch: frontend
gulp.task('watch:' + baseTask + ':frontend', function() {
	gulp.watch(extPath + '/site/**',
	['copy:' + baseTask + ':frontend']);
});

// Watch: backend
gulp.task('watch:' + baseTask + ':backend', function() {
	gulp.watch([
		extPath + '/admin/**',
		extPath + '/redeventb2b.xml',
		extPath + '/install.php'
	],
	['copy:' + baseTask + ':backend']);
});

// Watch: frontend
gulp.task('watch:' + baseTask + ':media', function() {
	gulp.watch(mediaPath + '/**',
		['copy:' + baseTask + ':media']);
});

// updates sites
gulp.task('update-sites:' + baseTask, function(){
	console.log('update-sites:' + baseTask + ' is not implemented yet');
});
