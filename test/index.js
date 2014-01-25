!function() {
  var aok = require('../node_modules/aok');
  var api = require('../');
  aok({ id: 'constructor', test: api() instanceof api });
}();