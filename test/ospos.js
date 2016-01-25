var assert = require('assert');

var ospos = function() {

    var server = "http://localhost";

    return {

        url : function(suffix) {
            return server + suffix;
        }
        ,
        login : function(browser, done) {
            return browser.get(this.url("/index.php"))
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

        }
    }
};

module.exports = ospos();