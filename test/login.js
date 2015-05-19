var assert = require("assert"); // node.js core module


describe("giftcard numbering test", function () {

    it("application login should work fine",  function (done) {
        this.browser.get('http://localhost/pos/index.php?XDEBUG_SESSION_START=ECLIPSE_DBGP&KEY=14241668456852')
            .elementByName('username').type("admin").getValue()
            .then(function (value) {
                assert.equal(value, "admin");
            })
            .elementByName('password').type("pointofsale").getValue()
            .then(function (value) {
                assert.ok(value, "pointofsale");
            })
            .elementByName('loginButton').click().done();
    });

    /*it("giftcard numbering should increment properly", function(done) {
        this.browser.elementById('home_module_list').then(function(value) {
            assert.ok(value, "Not logged in!!");
        }).elementsByCss('.menu_item').at(9).click().then(function(value) {
            assert.ok(value, "giftcards link not found!");
            for (var i = 0; i < 10; i++) {
                browser.elementByCss(".big_button").click().then(function (value) {
                    assert.ok(value, "No element found!");
                }).waitForElementById("value").type("100").then(function(value) {
                    assert.ok(value, "Giftcard value not set properly!");
                }).elementById("submit").click();
            }
        }).done();
    });*/

});
