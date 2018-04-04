/**
 * A reimplmentation RQL for JavaScript arrays based on rql/js-array from Kris Zyp (https://github.com/persvr/rql).
 * No more eval or new Function. (it has been made for Adobe Air projects. Adobe Air does not allow eval or new Function)
 *
 * Contains could also check if an array is contained in another array.
 *
 * Could handle dotted path for properties
 * 
 * @example
 * rql([{a:{b:3}},{a:3}], "a.b=3") -> [{a:{b:3}]
 * @author Gilles Coomans <gilles.coomans@gmail.com>
 */
(function(global) {
	"use strict";
	var parser = require('rql/parser');

	function inArray(what, inArr) {
		if (!inArr || !inArr.forEach)
			return false;
		if (what.forEach) {
			var test = {};
			inArr.forEach(function(e) {
				test[e] = true;
			});
			var okCount = 0;
			what.forEach(function(e) {
				if (test[e])
					okCount++;
			});
			if (okCount == what.length)
				return true;
			return false;
		}
		return inArr.some(function(e) {
			return what === e;
		});
	}

	var rqlParser = parser.parseQuery;

	var queryCache = {};

	var rql = function(array, query) {
		if (query[0] == "?")
			query = query.substring(1);
		if (queryCache[query])
			return queryCache[query].call(array);
		return rql.compile(query).call(array);
	};

	rql.parse = function(input) {
		try {
			var r = rqlParser(input);
			r.toString = function() {
				return input;
			};
			return r;
		} catch (e) {
			return null;
		}
	};

	rql.compile = function(query) {
		var parsed = rql.parse(query);
		var func = rqlNodeToFunc(parsed);
		queryCache[query] = func;
		return func;
	};
	var nextId = 1;
	rql.ops = {
		isPresent: function(path, items) {
			var res = [];
			var len = items.length;
			for (var i = 0; i < len; ++i)
				if (retrieve(items[i], path))
					res.push(items[i]);
			return res;
		},
		sort: function() {
			var terms = [];
			for (var i = 0; i < arguments.length; i++) {
				var sortAttribute = arguments[i];
				var firstChar = sortAttribute.charAt(0);
				var term = {
					attribute: sortAttribute,
					ascending: true
				};
				if (firstChar == "-" || firstChar == "+") {
					if (firstChar == "-")
						term.ascending = false;
					term.attribute = term.attribute.substring(1);
				}
				terms.push(term);
			}
			this.sort(function(a, b) {
				for (var i = 1, term = terms[0]; term; i++) {
					var ar = retrieve(a, term.attribute);
					var br = retrieve(b, term.attribute);
					if (ar != br)
						return term.ascending == ar > br ? 1 : -1;
					term = terms[i];
				}
				return 0;
			});
			return this;
		},
		match: filter(function(value, regex) {
			return new RegExp(regex).test(value);
		}),
		"in": filter(function(value, values) {
			var ok = false;
			var count = 0;
			while (!ok && count < values.length)
				if (values[count++] == value)
					ok = true;
			return ok;
		}),
		out: filter(function(value, values) {
			var ok = true;
			var count = 0;
			while (ok && count < values.length)
				if (values[count++] == value)
					ok = false;
			return ok;
		}),
		contains: filter(function(array, value) {
			return inArray(value, array);
		}),
		excludes: filter(function(array, value) {
			return !inArray(value, array);
		}),
		or: function() {
			// corrected with https://github.com/persvr/rql/commit/758ca34f91b7bcd18158bc34ffe0d42ab43747d8
			var items = [],
				idProperty = "__rqlId" + nextId++,
				i, l;
			try {
				for (i = 0; i < arguments.length; i++) {
					var group = arguments[i].call(this);
					l = group.length;
					for (var j = 0; j < l; j++) {
						var item = group[j];
						// use marker to do a union in linear time.
						if (!item[idProperty]) {
							item[idProperty] = true;
							items.push(item);
						}
					}
				}
			} finally {
				// cleanup markers
				for (i = 0, l = items.length; i < l; i++) {
					delete items[idProperty];
				}
			}
			return items;
		},
		and: function() {
			var items = this;
			for (var i = 0; i < arguments.length; ++i) {
				var a = arguments[i];
				if (typeof a == 'function')
					items = a.call(items);
				else
					items = rql.ops.isPresent(a, items);
			}
			return items;
		},
		select: function() {
			var args = arguments;
			var argc = arguments.length;
			var res = this.map(function(object) {
				var selected = {};
				for (var i = 0; i < argc; i++) {
					var propertyName = args[i];
					var value = evaluateProperty(object, propertyName);
					if (typeof value != "undefined")
						selected[propertyName] = value;
				}
				return selected;
			});
			return res;
		},
		unselect: function() {
			var args = arguments;
			var argc = arguments.length;
			return this.map(function(object) {
				var selected = {};
				for (var i in object)
					if (object.hasOwnProperty(i))
						selected[i] = object[i];
				for (var j = 0; j < argc; j++)
					delete selected[args[j]];
				return selected;
			});
		},
		values: function(first) {
			if (arguments.length == 1)
				return this.map(function(object) {
					return retrieve(object, first);
				});
			var args = arguments;
			var argc = arguments.length;
			return this.map(function(object) {
				var realObject = retrieve(object);
				var selected = [];
				if (argc === 0) {
					for (var i in realObject)
						if (realObject.hasOwnProperty(i))
							selected.push(realObject[i]);
				} else
					for (var j = 0; j < argc; j++) {
						var propertyName = args[j];
						selected.push(realObject[propertyName]);
					}
				return selected;
			});
		},
		limit: function(limit, start, maxCount) {
			var totalCount = this.length;
			start = start || 0;
			var sliced = this.slice(start, start + limit);
			if (maxCount) {
				sliced.start = start;
				sliced.end = start + sliced.length - 1;
				sliced.totalCount = Math.min(totalCount, typeof maxCount === "number" ? maxCount : Infinity);
			}
			return sliced;
		},
		distinct: function() {
			var primitives = {};
			var needCleaning = [];
			var newResults = this.filter(function(value) {
				value = retrieve(value);
				if (value && typeof value == "object") {
					if (!value.__found__) {
						value.__found__ = function() {}; // get ignored by JSON serialization
						needCleaning.push(value);
						return true;
					}
					return false;
				}
				if (!primitives[value]) {
					primitives[value] = true;
					return true;
				}
				return false;
			});
			needCleaning.forEach(function(object) {
				delete object.__found__;
			});
			return newResults;
		},
		recurse: function(property) {
			var newResults = [];

			function recurse(value) {
				if (value.forEach)
					value.forEach(recurse);
				else {
					newResults.push(value);
					if (property) {
						value = value[property];
						if (value && typeof value == "object")
							recurse(value);
					} else
						for (var i in value)
							if (value[i] && typeof value[i] == "object")
								recurse(value[i]);
				}
			}
			recurse(retrieve(this));
			return newResults;
		},
		aggregate: function() {
			var distinctives = [];
			var aggregates = [];
			for (var i = 0; i < arguments.length; i++) {
				var arg = arguments[i];
				if (typeof arg === "function")
					aggregates.push(arg);
				else
					distinctives.push(arg);
			}
			var distinctObjects = {};
			var dl = distinctives.length;
			this.forEach(function(object) {
				object = retrieve(object);
				var key = "";
				for (var i = 0; i < dl; i++)
					key += '/' + object[distinctives[i]];
				var arrayForKey = distinctObjects[key];
				if (!arrayForKey)
					arrayForKey = distinctObjects[key] = [];
				arrayForKey.push(object);
			});
			var al = aggregates.length;
			var newResults = [];
			for (var key in distinctObjects) {
				var arrayForKey = distinctObjects[key];
				var newObject = {};
				for (var j = 0; j < dl; j++) {
					var property = distinctives[j];
					newObject[property] = arrayForKey[0][property];
				}
				for (var k = 0; k < al; k++) {
					var aggregate = aggregates[k];
					newObject[k] = aggregate.call(arrayForKey);
				}
				newResults.push(newObject);
			}
			return newResults;
		},
		between: filter(function(value, range) {
			value = retrieve(value);
			return value >= range[0] && value < range[1];
		}),
		sum: reducer(function(a, b) {
			return retrieve(a) + retrieve(b);
		}),
		mean: function(property) {
			return rql.ops.sum.call(this, property) / this.length;
		},
		max: reducer(function(a, b) {
			return Math.max(retrieve(a), retrieve(b));
		}),
		min: reducer(function(a, b) {
			return Math.min(retrieve(a), retrieve(b));
		}),
		count: function() {
			return this.length;
		},
		first: function() {
			return this[0];
		},
		last: function() {
			return this[this.length - 1];
		},
		random: function() {
			return this[Math.round(Math.random() * (this.length - 1))];
		},
		one: function() {
			if (this.length > 1)
				throw new Error("RQLError : More than one object found");
			return this[0];
		}
	};

	function rqlNodeToFunc(node) {
		if (typeof node === 'object') {
			var name = node.name;
			var args = node.args;
			if (node.forEach)
				return node.map(rqlNodeToFunc);
			else {
				var b = args[0],
					path = null;
				if (args.length == 2) {
					path = b;
					b = args[1];
				}
				var func = null;
				var isFilter = false;
				switch (name) {
					case "eq":
						isFilter = true;
						func = function eq(a) {
							return (retrieve(a, path) || undefined) === b;
						};
						break;
					case "ne":
						isFilter = true;
						func = function ne(a) {
							return (retrieve(a, path) || undefined) !== b;
						};
						break;
					case "le":
						isFilter = true;
						func = function le(a) {
							return (retrieve(a, path) || undefined) <= b;
						};
						break;
					case "ge":
						isFilter = true;
						func = function ge(a) {
							return (retrieve(a, path) || undefined) >= b;
						};
						break;
					case "lt":
						isFilter = true;
						func = function lt(a) {
							return (retrieve(a, path) || undefined) < b;
						};
						break;
					case "gt":
						isFilter = true;
						func = function gt(a) {
							return (retrieve(a, path) || undefined) > b;
						};
						break;
					default:
						var ops = rql.ops[name];
						if (!ops)
							throw new Error("RQLError : no operator found in rql with : " + name);
						if (args && args.length > 0) {
							args = args.map(rqlNodeToFunc);
							func = function() {
								return ops.apply(this, args);
							};
						} else
							func = function() {
								return ops.call(this);
							};
				}
				if (isFilter)
					return function() {
						var r = this.filter(func);
						return r;
					};
				else
					return func;
			}
		} else
			return node;
	}

	function retrieve(obj, path) {
		if (!path)
			return obj;
		var splitted = path.split(".");
		var tmp = obj;
		if (!tmp)
			return;
		var count = 0,
			part = splitted[count];
		while (part && tmp[part]) {
			tmp = tmp[part];
			part = splitted[++count];
		}
		if (count === splitted.length)
		// manage Date as in https://github.com/persvr/rql/commit/117a7c94caf9ac99b263c01af6008af61b902f2f
			return (tmp instanceof Date) ? tmp.valueOf() : tmp;
		return;
	}

	function filter(condition, not) {
		var filtr = function(property, second) {
			if (typeof second == "undefined") {
				second = property;
				property = undefined;
			}
			var filtered = [];
			for (var i = 0, length = this.length; i < length; i++) {
				var item = this[i];
				if (condition(evaluateProperty(item, property), second))
					filtered.push(item);
			}
			return filtered;
		};
		filtr.condition = condition;
		return filtr;
	}

	function reducer(func) {
		return function(property) {
			if (property)
				return this.map(function(object) {
					return retrieve(object, property);
				}).reduce(func);
			else
				return this.reduce(func);
		};
	}

	function evaluateProperty(object, property) {
		if (property && property.forEach)
			return retrieve(object, decodeURIComponent(property));
		if (typeof property === 'undefined')
			return retrieve(object);
		return retrieve(object, decodeURIComponent(property));
	}
	if (typeof module !== 'undefined' && module.exports)
		module.exports = rql;
	else
		global.orql = rql;
})(this);
