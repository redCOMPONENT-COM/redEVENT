var gulp = require('gulp');

// Load config
var config = require('../../gulp-config.json');

// Dependencies
var browserSync = require('browser-sync');
var minifyCSS   = require('gulp-minify-css');
var rename      = require('gulp-rename');
var del         = require('del');
var zip         = require('gulp-zip');
var uglify      = require('gulp-uglify');

module.exports.addPlugin = function (group, name) {
	var baseTask  = 'plugins.' + group + '.' + name;
	var extPath   = './plugins/' + group + '/' + name;
	var mediaPath = extPath + '/media';

	// Clean
	gulp.task('clean:' + baseTask, function(cb) {
		del(config.wwwDir + '/plugins/' + group + '/' + name, {force : true}, cb);
	});

	// Clean: Media
	gulp.task('clean:' + baseTask + ':media', function(cb) {
		del(config.wwwDir + '/media/' + 'plg_' + group + '_' + name, {force: true}, cb);
	});

	// Copy
	gulp.task('copy:' + baseTask, ['clean:' + baseTask], function() {
		return gulp.src([
			extPath + '/**',
			'!' + extPath + '/media',
			'!' + extPath + '/media/**'
		])
			.pipe(gulp.dest(config.wwwDir + '/plugins/' + group + '/' + name))
			.pipe(browserSync.reload({stream:true}));
	});

	// Copy: media
	gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
		return gulp.src([
			mediaPath + '/**'
		])
			.pipe(gulp.dest(config.wwwDir + '/media/' + 'plg_' + group + '_' + name))
			.pipe(browserSync.reload({stream:true}));
	});

	// Watch
	gulp.task('watch:' + baseTask,
		[
			'watch:' + baseTask + ':plugin',
			'watch:' + baseTask + ':media'
		],
		function() {
		});

	// Watch: plugin
	gulp.task('watch:' + baseTask + ':plugin', function() {
		gulp.watch(extPath + '/**', ['copy:' + baseTask]);
	});

	// Watch: Media
	gulp.task('watch:' + baseTask + ':media', function() {
		gulp.watch([
			extPath + '/media/**'
		], ['copy:' + baseTask + ':media']);
	});
}
