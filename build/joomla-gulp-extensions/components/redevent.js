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
var replace     = require('gulp-replace');
var xml2js      = require('xml2js');
var parser      = new xml2js.Parser();

var baseTask  = 'components.redevent';
var extPath   = '../component';
var updateXmlPath = '../../update_server_xml/';
var mediaPath = extPath + '/media/com_redevent';
var pluginsPath = extPath + '/plugins';

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':frontend',
		'clean:' + baseTask + ':backend',
		'clean:' + baseTask + ':media',
		'clean:' + baseTask + ':plugins'
	],
	function() {
		return true;
});

// Clean: frontend
gulp.task('clean:' + baseTask + ':frontend', function() {
	del.sync(config.wwwDir + '/components/com_redevent', {force : true});
});

// Clean: backend
gulp.task('clean:' + baseTask + ':backend', function() {
	del.sync(config.wwwDir + '/administrator/components/com_redevent', {force : true});
});

// Clean: media
gulp.task('clean:' + baseTask + ':media', function() {
	del.sync(config.wwwDir + '/media/com_redevent', {force : true});
});

// Clean: plugins
gulp.task('clean:' + baseTask + ':plugins', function() {
	del.sync(config.wwwDir + '/plugins/redform_integration/redevent', {force : true});
});

// Copy
gulp.task('copy:' + baseTask,
	[
		'copy:' + baseTask + ':frontend',
		'copy:' + baseTask + ':backend',
		'copy:' + baseTask + ':media',
		'copy:' + baseTask + ':plugins'
	],
	function() {
		return true;
});

// Copy: frontend
gulp.task('copy:' + baseTask + ':frontend', ['clean:' + baseTask + ':frontend'], function() {
	console.log(extPath + '/site/**');
	return gulp.src(extPath + '/site/**')
		.pipe(gulp.dest(config.wwwDir + '/components/com_redevent'));
});

// Copy: backend
gulp.task('copy:' + baseTask + ':backend', ['clean:' + baseTask + ':backend'], function(cb) {
	return (
		gulp.src([
			extPath + '/admin/**'
		])
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redevent')) &&
		gulp.src(extPath + '/redevent.xml')
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redevent')) &&
		gulp.src(extPath + '/install.php')
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redevent'))
	);
});

// Copy: media
gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
	return gulp.src(mediaPath + '/**')
		.pipe(gulp.dest(config.wwwDir + '/media/com_redevent'));
});

// Copy: plugins
gulp.task('copy:' + baseTask + ':plugins', ['clean:' + baseTask + ':plugins'], function() {
	return gulp.src(pluginsPath + '/**')
		.pipe(gulp.dest(config.wwwDir + '/plugins'));
});

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':frontend',
		'watch:' + baseTask + ':backend',
		'watch:' + baseTask + ':plugins',
		'watch:' + baseTask + ':media'
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
		extPath + '/redevent.xml',
		extPath + '/install.php'
	],
	['copy:' + baseTask + ':backend']);
});

// Watch: plugins
gulp.task('watch:' + baseTask + ':plugins', function() {
	gulp.watch(extPath + '/plugins/**',
		['copy:' + baseTask + ':plugins']);
});

// Watch: plugins
gulp.task('watch:' + baseTask + ':media', function() {
	gulp.watch(extPath + '/media/**',
		['copy:' + baseTask + ':media']);
});

gulp.task('update-sites:' + baseTask, function(){
	fs.readFile( extPath + '/redevent.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			const version = result.extension.version[0];
			gulp.src(['./update_server_xml/com_redevent.xml'])
				.pipe(replace(/<version>(.*)<\/version>/g, "<version>" + version + "</version>"))
				.pipe(gulp.dest('./update_server_xml'));
		});
	});
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

