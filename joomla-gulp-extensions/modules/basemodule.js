var gulp = require('gulp');

var config = require('../../gulp-config.json');

// Dependencies
var browserSync = require('browser-sync');
var minifyCSS   = require('gulp-minify-css');
var rename      = require('gulp-rename');
var del         = require('del');
var less        = require('gulp-less');
var uglify      = require('gulp-uglify');
var zip         = require('gulp-zip');

module.exports.addModule = function (name) {
	var baseTask  = 'modules.frontend.' + name;
	var extPath   = './modules/site/' + name;
	var mediaPath = extPath + '/media';

	// Clean
	gulp.task('clean:' + baseTask, ['clean:' + baseTask + ':media'], function(cb) {
		del(config.wwwDir + '/modules/' + name, {force: true}, cb);
	});

	// Clean: Media
	gulp.task('clean:' + baseTask + ':media', function(cb) {
		del(config.wwwDir + '/media/' + name, {force: true}, cb);
	});

	// Copy
	gulp.task('copy:' + baseTask, ['clean:' + baseTask, 'copy:' + baseTask + ':media'], function() {
		return gulp.src([
			extPath + '/**',
			'!' + extPath + '/media',
			'!' + extPath + '/media/**'
		])
			.pipe(gulp.dest(config.wwwDir + '/modules/' + name))
			.pipe(browserSync.reload({stream:true}));
	});

	// Copy: media
	gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
		return gulp.src([
			mediaPath + '/**'
		])
			.pipe(gulp.dest(config.wwwDir + '/media/' + name))
			.pipe(browserSync.reload({stream:true}));
	});

	// Release module
	gulp.task('release:' + baseTask, function (){
		var extension = require('../../../package.json');

		gulp.src([extPath + '/**/*'])
			.pipe(zip(extension.name + '-v' + extension.version + '.zip'))
			.pipe(gulp.dest('releases'));
	});

	// Watch
	gulp.task('watch:' + baseTask,
		[
			'watch:' + baseTask + ':module',
			'watch:' + baseTask + ':media'
		],
		function() {
		});

	// Watch: Module
	gulp.task('watch:' + baseTask + ':module', function() {
		gulp.watch([
			extPath + '/**',
			'!' + extPath + '/media',
			'!' + extPath + '/media/**'
		], ['copy:' + baseTask]);
	});

	// Watch: Module
	gulp.task('watch:' + baseTask + ':media', function() {
		gulp.watch([
			extPath + '/media/**'
		], ['copy:' + baseTask + ':media']);
	});
};
