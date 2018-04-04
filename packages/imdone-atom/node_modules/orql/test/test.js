/**
 * @author Gilles Coomans <gilles.coomans@gmail.com>
 *
 */
if (typeof require !== 'undefined')
	var chai = require("chai"),
		orql = require("../index");

var expect = chai.expect;

describe("dotted path", function() {

	var res = orql([{
		a: {
			b: 3
		}
	}, {
		a: 3
	}], "a.b=3");

	it("should", function() {
		expect(res).to.deep.equals([{
			a: {
				b: 3
			}
		}]);
	});
});
