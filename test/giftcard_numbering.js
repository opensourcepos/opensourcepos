var assert = require("assert"); // node.js core module

describe("giftcard numbering test", function () {

    var server = "http://localhost/pos";

    var url = function url(suffix) {
        return server + suffix + "?XDEBUG_SESSION_START=ECLIPSE_DBGP&KEY=14241668456852'";
    };

    it("should be able to login",  function (done) {
        return this.browser.get(url("/index.php"))
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

    it.skip("issue #65: giftcard numbering should add properly", function() {
        return this.browser.get(url("/index.php/giftcards")).waitForElementByCss(".big_button").click()
            .waitForElementByName("value", 4000).type("100").elementById('giftcard_number').clear().type("10")
            .elementById("submit").click().waitForElementByXPath("//table/tbody/tr[td/text()='10']/td[4]", 2000).text().then(function (value) {
                assert.ok(value, "giftcard failed to be added properly!");
            }).elementByCss(".big_button").click().waitForElementByName("value", 4000).type("100").elementById("submit").click()
            .waitForElementByXPath("//table/tbody/tr[td/text()='11']/td[4]").text().then(function (value) {
                assert.equal(value, "11", "giftcard number not incrementing properly!!");
            });
    });

});
