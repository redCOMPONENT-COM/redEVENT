const gulp        = require('gulp');
const requireDir  = require('require-dir');
const jgulp       = requireDir('./node_modules/joomla-gulp', {recurse: true});

gulp.task('update-sites', ['update-sites:components', 'update-sites:modules', 'update-sites:plugins']);

gulp.task('update-sites:components',
    jgulp.src.components.getComponentsTasks('update-sites:components')
);

gulp.task('update-sites:modules',
    jgulp.src.modules.getModulesTasks('update-sites:modules', 'frontend')
);

gulp.task('update-sites:plugins',
    jgulp.src.plugins.getPluginsTasks('update-sites:plugins')
);