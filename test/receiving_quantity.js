var assert = require('assert');
var ospos = require('./ospos');

describe("test receiving quantity", function() {

    var def_timeout = 10000;

    var item = {
        name: "recvQty",
        category: "aCategory",
        cost_price: 10,
        unit_price: 20,
        receiving_quantity: 2
    };

    it("should be able to create item with receiving quantity", function(done) {
        return ospos.create_item(this.browser, item).then(done, done);
    });

    it("should be able to receive quantities with multiplier", function(done) {
        this.browser.get(ospos.url('/index.php/receivings')).elementById("item").clear().type(item.name)
            .elementByCssSelector(".ui-autocomplete .ui-menu-item", def_timeout).click()
            .elementByName("quantity")
            .elementByCssSelector("#cart_contents tr td:nth-child(5)").text().then(function(value) {
                assert(value, "x "  + item.receiving_quantity, "receiving quantity " + item.receiving_quantity + " is not displayed correctly in receivings module!");
            }).elementById("finish_receiving_button", def_timeout).submit()
            .elementByCssSelector("#receipt_items tr:nth-child(2) td:nth-last-child(2)", def_timeout).text().then(function(value) {
                assert(value, "1    x " + item.receiving_quantity, "receiving quantity " + item.receiving_quantity + " is not displayed correctly on receipt!!");
            }).then(done, done);
    });

});