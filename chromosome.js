(function(make) {
    module.exports = make();
}(function() {
    var curr
      , implement = api.prototype = Api.prototype
      , fs = require('fs')
      , path = require('path')
      , isFile = function(s) {
            return fs.lstatSync(s).isFile();
        }
      , isDir = function(s) {
            return fs.lstatSync(s).isDirectory();
        }
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
      , jsonName = 'basename:json'
      , dirData = {'type': 'dir'}
      , assign = Object.assign;

    /**
     * @constructor
     */
    function Api(data) {
        this._data = Object.create(null);
        api.context(this);
        if (null != data) {
            isPath(data) && (this.dir = path.dirname(
                data = isFile(data) ? data : path.join(data, api.option(jsonName))
            )) && (data = isFile(data) ? readJson(data) : dirData);
            data && this.data(data);
            //api.emit('normalize');
        }
    }
    
    function api(data) {
        return new Api(data);
    }
    implement = api.prototype = Api.prototype;
    
    api.context = function(ob) {
        return curr = null == ob ? curr || api() : ob instanceof Api ? ob : api(ob);
    };

    implement.data = function(k, v) {
        var cache = this._data, pair = 1 < arguments.length;
        if (typeof k == 'function') k = k.call(this, cache);
        if (null == k) return pair ? void 0 : cache;
        if (k === Object(k)) return assign(cache, k);
        return pair ? cache[k] = v : cache[k];
    };

    api.option = implement.data.bind(api);

    return api;
}));