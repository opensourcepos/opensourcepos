var assert = require('assert');
var ospos = require('./ospos');

describe("create item and make sale", function () {
    this.timeout(25000);

    it("should be able to add item", function (done) {
        return this.browser.get(ospos.url("/index.php/items")).elementByCssSelector("a[title='New Item']", 5000).click()
            .waitForElementByName("name", 10000).type("anItem").elementById("category").type("aCategory")
            .elementById('cost_price', 2000).clear().type("10").elementById("unit_price", 2000).type("20")
            .elementById('tax_name_1', 2000).type('VAT').elementById("tax_percent_name_1", 2000).type("21")
            .elementById("1_quantity", 2000).type("1").elementById("reorder_level", 2000).type("0").elementById("submit", 2000).click()
            .waitForElementByXPath("//table/tbody/tr[td/text()='anItem']/td[3]").text().then(function (value) {
                assert.equal(value, "anItem", "item could not be created!!");
            }).then(done, done);
    });

    it("should be able to make sale", function(done) {
        return this.browser.get(ospos.url("/index.php/sales")).elementById("item", 3000).type("1\r\n")
            .waitForElementByName("quantity", 5000).clear().type("2").elementByName("discount", 1000).type("10").elementByName("edit_item").click()
            .elementById("add_payment_button", 2000).click().elementByCssSelector("tbody#payment_contents tr td:nth-child(3)", 5000).text().then(function(value) {
                assert.equal(value, "$43.56", "discounted price " + value + " is not correct!!");
            }).then(done, done);
    });

});