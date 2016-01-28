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
        return this.browser.get(ospos.url("/index.php/sales"))
            .elementById("item", 3000).type("1\r\n")
            .waitForElementByName("quantity", 5000).clear().type("2").elementByName("discount", 1000).type("10").elementByName("edit_item").click()
            .elementById("add_payment_button", 2000).click().elementByCssSelector("tbody#payment_contents tr td:last-child", 5000).text().then(function(value) {
                assert.equal(value, "$43.56", "price " + value + " in sale register is not correct!!");
            }).elementById("finish_sale_button", 3000).submit().elementByCssSelector("#receipt_items tbody tr:nth-child(7) td:last-child", 5000).text().then(function(value) {
                assert.equal(value, "$43.56", "price " + value + " on sale receipt is not correct!!");
            }).elementByCssSelector("#receipt_items tbody tr:nth-child(9) td:last-child div.total-value", 5000).text().then(function(value) {
                assert.equal(value, "-$43.56", "payment amount " + value + " on sale receipt is not correct!!")
            }).then(done, done);
    });


    it("should be able to make receiving", function(done) {
        return this.browser.get(ospos.url("/index.php/receivings"))
            .elementById("item", 3000).type("1\r\n")
            .waitForElementByName("quantity", 3000).clear().type("2").elementByName("edit_item").click()
            .elementByCssSelector("td:nth-last-child(2)").text().then(function(value) {
                assert.equal(value, "$20.00", "price " + value + " in receiving register is not correct!!");
            }).elementById("finish_receiving_button").submit().elementByCssSelector("#receipt_items tbody tr:nth-last-child(2) td:nth-child(2) div.total-value").text().then(function(value) {
                assert.equal(value, "$20.00", "price " + value + " on receiving receipt is not correct!!");
            })
          .then(done, done);
    });


});