var assert = require('assert');
var ospos = require('./ospos');
require('wd');

describe("create item and make sale", function () {
    this.timeout(25000);

    var def_timeout = 3000;

    var item = {name: "anItem", category: "aCategory", cost_price: 10, unit_price: 20};

    it("should be able to add item", function (done) {
        return ospos.create_item(this.browser, item).then(done, done);
    });

    it("should be able to make sale", function(done) {
        return this.browser.get(ospos.url("/index.php/sales"))
            .elementById("item", def_timeout).clear().type("1\uE007")
            .elementByName("quantity", def_timeout).clear().type("2").elementByName("discount", def_timeout).type(item.cost_price).elementByCssSelector(".glyphicon.glyphicon-refresh").click()
            .elementById("add_payment_button", def_timeout).click().elementByCssSelector("tbody#payment_contents tr td:last-child", def_timeout).text().then(function(value) {
                assert.equal(value, "$43.56", "price " + value + " in sale register is not correct!!");
            }).elementById("finish_sale_button", def_timeout).click().elementByCssSelector("#receipt_items tbody tr:nth-child(8) td:last-child", def_timeout).text().then(function(value) {
                assert.equal(value, "$43.56", "price " + value + " on sale receipt is not correct!!");
            }).elementByCssSelector("#receipt_items tbody tr:nth-child(10) td:last-child", def_timeout).text().then(function(value) {
                assert.equal(value, "-$43.56", "payment amount " + value + " on sale receipt is not correct!!")
            }).then(done, done);
    });


    it("should be able to make receiving", function(done) {
        return this.browser.get(ospos.url("/index.php/receivings"))
            .elementById("item", def_timeout).clear().type("1\uE007")
            .elementByName("quantity", def_timeout).clear().type("2").elementByCssSelector("a[title='Update']").click()
            .elementByCssSelector("td:nth-last-child(2)").text().then(function(value) {
                assert.equal(value, "$20.00", "price " + value + " in receiving register is not correct!!");
            }).elementById("finish_receiving_button").click().elementByCssSelector("#receipt_items tbody tr:nth-last-child(2) td:nth-child(2) div.total-value").text().then(function(value) {
                assert.equal(value, "$20.00", "price " + value + " on receiving receipt is not correct!!");
            })
          .then(done, done);
    });


});