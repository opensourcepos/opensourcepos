var assert = require("assert"); // node.js core module
var wd = require('wd');

describe('A Mocha test run by grunt-mocha-webdriver', function () {
	it('has a browser injected into it', function () {
		assert.ok(this.browser);
	});
	it('has wd injected into it for customizing', function () {
        assert.notEqual(this.wd, undefined);
    });
});
