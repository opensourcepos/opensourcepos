var assert = require("assert"); // node.js core module

describe("giftcard numbering test", function () {

    it("giftcard numbering should increment",  function (done) {

        var searchBox;
        var browser = this.browser;
        browser.get('http://localhost/index.php?XDEBUG_SESSION_START=ECLIPSE_DBGP&KEY=14241668456852')
            .elementByName('username').type("admin").getValue()
            .then(function(value) {
                assert.equal(value, "admin");
            })
            .elementByName('password').type("pointofsale").getValue()
            .then(function(value) {
                assert.ok(value, "pointofsale");
            })
            .elementByName('loginButton').click()
            .elementById('home_module_list').then(function(value) {
                assert.ok(value, "Login failed!!")
            })
            .then(done, done);

    });

});
