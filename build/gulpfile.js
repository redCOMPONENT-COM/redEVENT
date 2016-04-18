var gulp = require('gulp');

var config      = require('./config.js');
var extension   = require('./package.json');
var requireDir  = require('require-dir');
var zip         = require('gulp-zip');
var xml2js      = require('xml2js');
var fs          = require('fs');
var path        = require('path');
var del         = require('del');
var exec        = require('child_process').exec;
var redcore     = requireDir('./redCORE/build/gulp-redcore', {recurse: true});
var replace     = require('gulp-replace');

var jgulp = requireDir('./node_modules/joomla-gulp', {recurse: true});
var dir = requireDir('./joomla-gulp-extensions', {recurse: true});

var parser      = new xml2js.Parser();

var gitDescribe = '';

var dateFormat = require('dateformat');
var now = new Date();

gulp.task('prepare:release', ['clean:release', 'git_version'], function(){
	return del(config.release_dir, {force: true});
});

gulp.task('clean:release', function(){
	return del(config.release_dir, {force: true});
});

gulp.task('git_version', function(){
	return getGitDescribe(function(str) {
		gitDescribe = str;
	});
});

// Override of the release script
gulp.task('release',
	[
		'release:redevent',
		'release:plugins',
		'release:languages',
		'release:redeventsync',
		'release:redeventb2b'
	], function() {
		fs.readFile( '../component/redevent.xml', function(err, data) {
			parser.parseString(data, function (err, result) {
				var version = getGitVersion(result);
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

gulp.task('release:redevent', ['prepare:release'], function (cb) {
	fs.readFile( '../component/redevent.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			var version = getGitVersion(result);
			var fileName = config.skipVersion ? extension.name + '.zip' : extension.name + '-v' + version + '.zip';

			// We will output where release package is going so it is easier to find
			console.log('Creating new release file in: ' + path.join(config.release_dir, fileName));
			gulp.src('../component/**/*')
				.pipe(replace(/(##VERSION##)/g, gitDescribe))
				// .pipe(replace(/(##DATE##)/g, dateFormat(now, "dddd, mmmm dS, yyyy")))
				.pipe(zip(fileName))
				.pipe(gulp.dest(config.release_dir))
				.on('end', cb);
		});
	});
});

gulp.task('release:redeventsync', ['prepare:release'], function (cb) {
	fs.readFile( '../redeventsync/redeventsync.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			var version = result.extension.version[0];
			var fileName = config.skipVersion ? 'redeventsync.zip' : 'redeventsync-v' + version + '.zip';

			// We will output where release package is going so it is easier to find
			console.log('Creating new release file in: ' + path.join(config.release_dir, fileName));
			gulp.src('../redeventsync/**/*')
				.pipe(zip(fileName))
				.pipe(gulp.dest(config.release_dir))
				.on('end', cb);
		});
	});
});

gulp.task('release:redeventb2b', ['prepare:release'], function (cb) {
	fs.readFile( '../redeventb2b/redeventb2b.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			var version = result.extension.version[0];
			var fileName = config.skipVersion ? 'redeventb2b.zip' : 'redeventb2b-v' + version + '.zip';

			// We will output where release package is going so it is easier to find
			console.log('Creating new release file in: ' + path.join(config.release_dir, fileName));
			gulp.src('../redeventb2b/**/*')
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
	jgulp.src.modules.getModulesTasks('release:modules', 'frontend')
);

gulp.task('release:languages', ['prepare:release'], function() {
	var langPath = '../languages';
	var releaseDir = path.join(config.release_dir, 'language');

	var folders = fs.readdirSync(langPath)
		.map(function(file){
			return path.join(langPath, file);
		})
		.filter(function(file) {
			return fs.statSync(file).isDirectory();
		});

	var tasks = folders.map(function(directory) {
		return fs.readFile(path.join(directory, 'install.xml'), function(err, data) {
			parser.parseString(data, function (err, result) {
				var lang = path.basename(directory);
				var version = result.extension.version[0];
				var fileName = config.skipVersion ? extension.name + '_' + lang + '.zip' : extension.name + '_' + lang + '-v' + version + '.zip';

				return gulp.src([
						directory + '/**'
					])
					.pipe(zip(fileName))
					.pipe(gulp.dest(releaseDir));
			});
		});
	});

	return tasks;
});

function getGitDescribe(cb) {
	exec('git describe', function (err, stdout, stderr) {
		cb(stdout.split('\n').join(''))
	})
}

function getGitVersion(xml) {
	if (!gitDescribe) {
		throw "git describe not initialized";
	}

	return gitDescribe;
}
