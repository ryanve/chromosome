(function(root, name, make) {
  typeof module != 'undefined' && module.exports ? module.exports = make() : root[name] = make();
}(this, 'chromosome', function() {

  var curr
    , model = api.prototype = Api.prototype
    , req = typeof require == 'function' && require
    , win = typeof window != 'undefined' && window
    , fs = req && req('fs')
    , path = req && req('path')
    , emits = req ? req(win ? 'emits' : './node_modules/emits') : win['emits']
    , isFile = function(s) {
        return fs.lstatSync(s).isFile();
      }
    //, isDir = function(s) {
    //    return fs.lstatSync(s).isDirectory();
    //}
    , isPath = function(s) {
        return fs.existsSync(s);
      }
    , isExt = function(s, ext) {
        return path.extname(s) === ext;
      }
    , readJson = function(p) {
        if (isExt(p, '.json')) require(p);
        else throw Error('.json expected');
      }
    , read = function(p) {
        return isPath(p) ? fs.readFileSync(p) : void 0;
      }
    , htmlName = 'basename:html'
    , jsonName = 'basename:json'
    , viewsPath = 'path:views'
    , dirData = {'type': 'dir'}
    , keys = Object.keys
    , create = Object.create
    , assign = function(to, from) {
      return keys(from).some(function(k) {
        to[k] = from[k];
      }), to;
    };

  /**
   * @constructor
   * @param {(string|Object)=} data
   */
  function Api(data) {
    this._data = create(null);
    api.context(this);
    if (null != data) {
      isPath(data) && (this.dir = path.dirname(
        data = isFile(data) ? data : path.join(data, api.option(jsonName))
      )) && (data = isFile(data) ? readJson(data) : dirData);
      data && this.data(data);
      api.emit('normalize', this);
    }
  }
  
  /**
   * @param {(string|Object)=} data
   * @return {Api}
   */
  function api(data) {
    return new Api(data);
  }

  // Build api into an emitter.
  emits.call(api);
  assign(api, emits.prototype);

  /**
   * @param {Object=} o
   * @return {Api} current instance
   */
  api.context = function(o) {
    return curr = null == o ? curr || api() : o instanceof Api ? o : api(o);
  };

  /**
   * @param {*=} k
   * @param {*=} v
   */
  model.data = function(k, v) {
    var cache = this._data, pair = 1 < arguments.length;
    if (typeof k == 'function') k = k.call(this, cache);
    if (null == k) return pair ? void 0 : cache;
    if (k === Object(k)) return assign(cache, k);
    return pair ? cache[k] = v : cache[k];
  };
  
  model.view = function(basename) {
    return read(path.join(api.option(viewsPath), null == basename ? 'default.html' : basename));
  };
  
  model.read = function(basename) {
    var p = path.join(this.dir, basename); // maybe resolve
    return isExt(p, '.json') ? require(p) || {} : read(p) || '';
  };
  
  model.render = function(view) {
    var data = this.data(htmlName, this.format(this.read(api.option(htmlName)))); // todo: imports
    return this.template(this.view(view), data);
  };

  api._data = create(null);
  api.option = model.data.bind(api);
  api.option(viewsPath, '_views');

  return api;
}));