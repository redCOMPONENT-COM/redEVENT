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

var baseTask  = 'components.redeventsync';
var extPath   = '../redeventsync';
var mediaPath = extPath + '/media/com_redeventsync';

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
	del.sync(config.wwwDir + '/components/com_redeventsync', {force : true});
});

// Clean: backend
gulp.task('clean:' + baseTask + ':backend', function() {
	del.sync(config.wwwDir + '/administrator/components/com_redeventsync', {force : true});
});

// Clean: media
gulp.task('clean:' + baseTask + ':media', function() {
	del.sync(config.wwwDir + '/media/com_redeventsync', {force : true});
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
		.pipe(gulp.dest(config.wwwDir + '/components/com_redeventsync'));
});

// Copy: backend
gulp.task('copy:' + baseTask + ':backend', ['clean:' + baseTask + ':backend'], function(cb) {
	return (
		gulp.src([
			extPath + '/admin/**'
		])
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redeventsync')) &&
		gulp.src(extPath + '/redeventsync.xml')
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redeventsync')) &&
		gulp.src(extPath + '/install.php')
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redeventsync'))
	);
});

// Copy: media
gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
	return gulp.src(mediaPath + '/**')
		.pipe(gulp.dest(config.wwwDir + '/media/com_redeventsync'));
});

// updates sites
gulp.task('update-sites:' + baseTask, function(){
	console.log('update-sites:' + baseTask + ' is not implemented yet');
});

//// Compile LESS
//gulp.task('less:' + baseTask, function () {
//	return gulp.src([
//			'./src/assets/com_redevent/less/backend.less',
//			'./src/assets/com_redevent/less/frontend.less'
//		])
//		.pipe(less({paths: ['./src/assets/less/com_redevent']}))
//		.pipe(gulp.dest(mediaPath + '/css'))
//		.pipe(gulp.dest(config.wwwDir + '/media/com_redevent/css'))
//		.pipe(browserSync.reload({stream:true}))
//		.pipe(minifyCSS())
//		.pipe(rename(function (path) {
//				path.basename += '.min';
//		}))
//		.pipe(gulp.dest(mediaPath + '/css'))
//		.pipe(gulp.dest(config.wwwDir + '/media/com_redevent/css'))
//		.pipe(browserSync.reload({stream:true}));
//});
//
//function scripts(src, ouputFileName) {
//	return gulp.src(src)
//		.pipe(concat(ouputFileName))
//		.pipe(gulp.dest(mediaPath + '/js'))
//		.pipe(gulp.dest(config.wwwDir + '/media/com_redevent/js'))
//		.pipe(browserSync.reload({stream:true}))
//		.pipe(uglify())
//		.pipe(rename(function (path) {
//			path.basename += '.min';
//		}))
//		.pipe(gulp.dest(mediaPath + '/js'))
//		.pipe(gulp.dest(config.wwwDir + '/media/com_redevent/js'))
//		.pipe(browserSync.reload({stream:true}));
//}
//
//// Backend scripts
//gulp.task('scripts:' + baseTask + ':backend', function () {
//	return scripts([
//		'./src/assets/vendor/jquery/dist/jquery.js',
//		'./src/assets/vendor/bootstrap/dist/js/bootstrap.js',
//		'./src/assets/vendor/admin-lte/dist/js/app.js',
//		'./src/assets/vendor/raphael/raphael-min.js',
//		'./src/assets/vendor/morris.js/morris.min.js',
//		'./src/assets/com_redevent/js/backend.js'
//	], 'backend.js');
//});
//
//// Frontend scripts
//gulp.task('scripts:' + baseTask + ':frontend', function () {
//	scripts([
//		'./src/assets/vendor/raphael/raphael-min.js',
//		'./src/assets/vendor/morris.js/morris.min.js',
//		'./src/assets/com_redevent/js/findbeacons.js',
//		'./src/assets/com_redevent/js/statistics.js'
//	], 'frontend.js');
//
//	scripts([
//		'./src/assets/com_redevent/js/mobile.js',
//	], 'mobile.js');
//
//	scripts('./src/assets/vendor/selectize/dist/js/standalone/selectize.min.js', 'selectize.min.js');
//});
//
//// Scripts
//gulp.task('scripts:' + baseTask, ['scripts:' + baseTask + ':backend', 'scripts:' + baseTask + ':frontend']);
//
//// Styles
//gulp.task('styles:' + baseTask, function () {
//	return gulp.src([
//			mediaPath + '/css/*.css',
//			'!' + mediaPath + '/css/*.min.css',
//			'./src/assets/vendor/morris.js/morris.css',
//			'./src/assets/vendor/selectize/dist/css/selectize.bootstrap3.css'
//		])
//		.pipe(gulp.dest(config.wwwDir + '/media/com_redevent/css'))
//		.pipe(browserSync.reload({stream:true}))
//		.pipe(minifyCSS())
//		.pipe(rename(function (path) {
//				path.basename += '.min';
//		}))
//		.pipe(gulp.dest(mediaPath + '/css'))
//		.pipe(gulp.dest(config.wwwDir + '/media/com_redevent/css'))
//		.pipe(browserSync.reload({stream:true}));
//});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':frontend',
		'watch:' + baseTask + ':backend'
		//'watch:' + baseTask + ':scripts',
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
		extPath + '/redeventsync.xml',
		extPath + '/install.php'
	],
	['copy:' + baseTask + ':backend']);
});

//// Watch: LESS
//gulp.task('watch:' + baseTask + ':less',
//	function() {
//		gulp.watch(
//			'./src/assets/com_redevent/less/**',
//			['less:' + baseTask]
//		);
//});
//
//// Watch: Scripts
//gulp.task('watch:' + baseTask + ':scripts', function() {
//	gulp.watch([
//		'./src/assets/com_redevent/js/*.js'
//	], ['scripts:' + baseTask]);
//});
//
//// Watch: Styles
//gulp.task('watch:' + baseTask + ':styles', function() {
//	gulp.watch([
//		mediaPath + '/css/*.css',
//		'!' + mediaPath + '/css/*.min.css'
//	], ['styles:' + baseTask]);
//});

