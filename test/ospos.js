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
        .then(function (value) {
            assert.equal(value, "admin");
        })
        .elementByName('password').type("pointofsale").getValue()
        .then(function (value) {
            assert.ok(value, "pointofsale");
        })
        .elementByName('loginButton').click()
        .elementById('home_module_list').then(function (value) {
            assert.ok(value, "Login failed!!")
        })
        .then(done, done);

    },

    create_item : function(browser, item)
    {
        return browser.get(this.url("/index.php/items")).elementByCssSelector("button[title*='New Item']", 5000).click()
        .elementById('cost_price', 2000).clear().type(item.cost_price)
        .elementById("unit_price", 2000).type(item.unit_price)
        .elementById('tax_name_1', 2000).type('VAT').elementById("tax_percent_name_1", 2000).type("21")
        .elementById("name", 10000).type(item.name)
        .elementById("category", 2000).clear().type(item.category)
        .elementById('receiving_quantity', 2000).type(item.receiving_quantity || 1)
        .elementById("quantity_1", 2000).type("1").elementById("reorder_level", 2000).type("0").elementById("submit", 2000).click()
        .elementByXPath("//table/tbody/tr[td/text()='anItem']", 5000).text().then(function (value) {
            assert.equal(value, "1 - anItem aCategory - $10.00 $20.00 1 21.00%");
        });
    }
    }
};

module.exports = ospos();
