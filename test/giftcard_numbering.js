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

    for (var i=1; i < 12; i++) {

        var evaluate = (function(i, browser) {
          return function() {
              return this.browser.get(url("/index.php/giftcards")).waitForElementByCss(".big_button").click()
                  .waitForElementByName("value").type("100")
                  .elementById("submit").click().waitForElementById("giftcard_" + i, 100, 2000).then(function(value) {
                      assert.ok(value, "Giftcard failed to be added properly!");
              });
        }})(i);
        it("gitftcard numbering should add up fine " + i, evaluate);
    }


});
