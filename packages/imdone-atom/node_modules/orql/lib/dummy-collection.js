var rql = require('../index');

// for patch
function setProp(to, path, value) {
	var tmp = to,
		i = 0,
		old,
		len = path.length - 1;
	for (; i < len; ++i)
		if (tmp && !tmp[path[i]])
			tmp = tmp[path[i]] = {};
		else
			tmp = tmp[path[i]];
	if (tmp) {
		old = tmp[path[i]];
		tmp[path[i]] = value;
	}
	return old;
}

var DummyCollection = function(array) {
	this._collection = array;
};

DummyCollection.prototype = {
	get: function(req, opt) {
		if (req === '' || req === '?')
			return this._collection;
		if (req[0] === '?')
			return rql(this._collection, req.substring(1));
		return rql(this._collection, 'id=' + req)[0];
	},
	post: function(document, opt) {
		document.id = document.id || ('id' + new Date().valueOf());
		this._collection.push(document);
		return document;
	},
	put: function(document, opt) {
		var i = 0,
			len = this._collection.length;
		for (; i < len; ++i) {
			var doc = this._collection[i];
			if (doc.id === document.id)
				break;
		}
		if (i === len)
			throw new Error('no document found with ' + document.id + ' for "put"');
		this._collection[i] = document;
		return document;
	},
	patch: function(id, path, fragment, opt) {
		var doc = rql(this._collection, 'id=' + id)[0];
		if (!doc)
			throw new Error('no document found with ' + id + ' for "patch"');
		setProp(doc, path, fragment);
		return doc;
	},
	del: function(id) {
		var i = 0,
			len = this._collection.length;
		for (; i < len; ++i) {
			var doc = this._collection[i];
			if (doc.id === id)
				break;
		}
		if (i === len)
			throw new Error('no document found with ' + id + ' for "put"');
		this._collection.splice(i, 1);
		return true;
	}
};

module.exports = DummyCollection;
