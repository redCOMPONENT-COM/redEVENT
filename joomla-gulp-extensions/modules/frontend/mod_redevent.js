var base = require('../basemodule');
var path = require('path');

var name = path.basename(__filename).replace('.js', '');

base.addModule(name);
