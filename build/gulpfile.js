const gulp = require('gulp');

const config      = require('./config.js');
const extension   = require('./package.json');
const requireDir  = require('require-dir');
const zip         = require('gulp-zip');
const xml2js      = require('xml2js');
const fs          = require('fs');
const path        = require('path');
const del         = require('del');
const exec        = require('child_process').exec;
const replace     = require('gulp-replace');
const filter      = require('gulp-filter');
const merge       = require('merge-stream');

const jgulp = requireDir('./node_modules/joomla-gulp', {recurse: true});
const dir = requireDir('./joomla-gulp-extensions', {recurse: true});

const update_sites = require('./update-sites.js');
const bump_version = require('./bump-version.js');

const parser      = new xml2js.Parser();

var gitDescribe = '';

gulp.task('prepare:release', ['clean:release', 'git_version']);

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
		'release:modules',
		'release:plugins',
		'release:languages',
		'release:redeventsync',
		'release:redeventb2b'
	], function() {
		fs.readFile( '../component/redevent.xml', function(err, data) {
			parser.parseString(data, function (err, result) {
				const version = gitDescribe;
				const fileName = config.skipVersion ? extension.name + '_ALL_UNZIP_FIRST.zip' : extension.name + '-v' + version + '_ALL_UNZIP_FIRST.zip';
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
			const version = gitDescribe;
			const fileName = config.skipVersion ? extension.name + '.zip' : extension.name + '-v' + version + '.zip';
			const xmlFilter = filter(['../**/*.xml'], {restore: true});

			// We will output where release package is going so it is easier to find
			console.log('Creating new release file in: ' + path.join(config.release_dir, fileName));

			gulp.src('../component/**/*')
				.pipe(xmlFilter)
				.pipe(replace(/(##VERSION##)/g, version))
				.pipe(xmlFilter.restore)
				.pipe(zip(fileName))
				.pipe(gulp.dest(config.release_dir))
				.on('end', cb);
		});
	});
});

gulp.task('release:redeventsync', ['prepare:release'], function (cb) {
	fs.readFile( '../redeventsync/redeventsync.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			const version = result.extension.version[0];
			const fileName = config.skipVersion ? 'redeventsync.zip' : 'redeventsync-v' + version + '.zip';
			const xmlFilter = filter(['../**/*.xml'], {restore: true});

			// We will output where release package is going so it is easier to find
			console.log('Creating new release file in: ' + path.join(config.release_dir, fileName));
			gulp.src('../redeventsync/**/*')
				.pipe(xmlFilter)
				.pipe(replace(/(##VERSION##)/g, version))
				.pipe(xmlFilter.restore)
				.pipe(zip(fileName))
				.pipe(gulp.dest(config.release_dir))
				.on('end', cb);
		});
	});
});

gulp.task('release:redeventb2b', ['prepare:release'], function (cb) {
	fs.readFile( '../redeventb2b/redeventb2b.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			const version = result.extension.version[0];
			const fileName = config.skipVersion ? 'redeventb2b.zip' : 'redeventb2b-v' + version + '.zip';
			const xmlFilter = filter(['../**/*.xml'], {restore: true});

			// We will output where release package is going so it is easier to find
			console.log('Creating new release file in: ' + path.join(config.release_dir, fileName));
			gulp.src('../redeventb2b/**/*')
				.pipe(xmlFilter)
				.pipe(replace(/(##VERSION##)/g, version))
				.pipe(xmlFilter.restore)
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
	const langPath = '../languages';
	const releaseDir = path.join(config.release_dir, 'language');

	const folders = fs.readdirSync(langPath)
		.map(function(file) {
			return path.join(langPath, file);
		})
		.filter(function(file) {
			return fs.existsSync(path.join(file, 'install.xml'));
		});

	// We need to combine streams so we can know when this task is actually done
	return merge(folders.map(function(directory) {
			const data = fs.readFileSync(path.join(directory, 'install.xml'));

			// xml2js parseString is sync, but must be called using callbacks... hence this awkwards vars
			// see https://github.com/Leonidas-from-XIV/node-xml2js/issues/159
			var task;
			var error;

			parser.parseString(data, function (err, result) {
				if (err) {
					error = err;
					console.log(err);

					return;
				}

				const lang = path.basename(directory);
				const version = result.extension.version[0];
				const fileName = config.skipVersion ? extension.name + '_' + lang + '.zip' : extension.name + '_' + lang + '-v' + version + '.zip';

				task = gulp.src([directory + '/**'])
					.pipe(zip(fileName))
					.pipe(gulp.dest(releaseDir));
			});

			if (error) {
				throw error;
			}

			if (!error && !task) {
				throw new Error('xml2js callback became suddenly async or something.');
			}

			return task;
		})
	);
});

gulp.task('insert-update-site', ['insert-update-site:modules']);

gulp.task('insert-update-site:modules',
	jgulp.src.modules.getModulesTasks('insert-update-site:modules', 'frontend'));

function getGitDescribe(cb) {
	exec('git describe', function (err, stdout, stderr) {
		cb(stdout.split('\n').join(''))
	})
}
