<?php
/**
 * @backupGlobals disabled
 */
class Barcode_lib_test extends UnitTestCase
{
    public function setUp()
    {
        $this->resetInstance();

        $this->obj = $this->newLibrary('Barcode_lib');
        $this->CI->Token_lib = $this->newLibrary('Token_lib');

        ReflectionHelper::setPrivateProperty(
            $this->obj,
            'CI',
            $this->CI
        );

    }

    public function test_barcode_item_number_and_quantity()
    {
        $this->CI->config->set_item('barcode_formats', json_encode(array("02{I:5}{W:6}")));

        $item_number = "0250000123456";
        $quantity = 0;
        $this->obj->parse_barcode_fields($quantity, $price, $item_number);

        $this->assertEquals($quantity, 123.456);
        $this->assertEquals($item_number, "50000");
        $this->assertEquals($price, NULL);
    }

    public function test_barcode_quantity_and_item_number()
    {
        $this->CI->config->set_item('barcode_formats', json_encode(array("02{I:6}{W:5}{P:2}")));

        $item_number = "021234565000110";
        $quantity = 0;
        $this->obj->parse_barcode_fields($quantity, $price, $item_number);


        $this->assertEquals($price, 10);
        $this->assertEquals($item_number, 123456);
        $this->assertEquals($quantity, 50.001);
    }

    public function test_no_barcode_format()
    {
        $this->CI->config->set_item('barcode_formats', json_encode(array("")));

        $item_number = "021234565000110";
        $quantity = 0;

        $this->obj->parse_barcode_fields($quantity, $price, $item_number);

        $this->assertEquals($quantity, 1);
        $this->assertEquals($price, 0);
        $this->assertEquals($item_number, "021234565000110");
    }
}