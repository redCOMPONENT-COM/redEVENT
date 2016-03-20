var gulp = require('gulp');

var config      = require('./config.js');
var extension   = require('./package.json');
var requireDir  = require('require-dir');
var zip         = require('gulp-zip');
var xml2js      = require('xml2js');
var fs          = require('fs');
var path        = require('path');
var del         = require('del');
var redcore     = requireDir('./redCORE/build/gulp-redcore', {recurse: true});

var jgulp = requireDir('./node_modules/joomla-gulp', {recurse: true});
var dir = requireDir('./joomla-gulp-extensions', {recurse: true});

var parser      = new xml2js.Parser();

// Override of the release script
gulp.task('release',
	[
		'release:redevent',
		'release:plugins'
		//'release:languages'
	], function() {
		fs.readFile( '../component/redevent.xml', function(err, data) {
			parser.parseString(data, function (err, result) {
				var version = result.extension.version[0];
				var fileName = config.skipVersion ? extension.name + '_ALL_UNZIP_FIRST.zip' : extension.name + '-v' + version + '_ALL_UNZIP_FIRST.zip';
				del.sync(path.join(config.release_dir, fileName), {force: true});

				// We will output where release package is going so it is easier to find
				console.log('Creating all in one release file in: ' + path.join(config.release_dir, fileName));
				return gulp.src([
						config.release_dir + '/**',
						'!' + fileName
					])
					.pipe(zip(fileName))
					.pipe(gulp.dest(config.release_dir));
			});
		});
	}
);

gulp.task('release:redevent', function (cb) {
	fs.readFile( '../component/redevent.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			var version = result.extension.version[0];
			var fileName = config.skipVersion ? extension.name + '.zip' : extension.name + '-v' + version + '.zip';

			// We will output where release package is going so it is easier to find
			console.log('Creating new release file in: ' + path.join(config.release_dir, fileName));
			gulp.src('../component/**/*')
				.pipe(zip(fileName))
				.pipe(gulp.dest(config.release_dir))
				.on('end', cb);
		});
	});
});

gulp.task('release:plugins',
	jgulp.src.plugins.getPluginsTasks('release:plugins')
);

gulp.task('release:modules',
	jgulp.src.modules.getModulesTasks('release:modules', 'site')
);
