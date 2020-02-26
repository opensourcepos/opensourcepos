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
	}

	public function test_barcode_weight_first()
	{
		$config = $this->getMockBuilder('CI_Config')
			->disableOriginalConstructor()
			->setMethods(['item'])
			->getMock();
		$config->method('item')
			->willReturn("02(\d{5})(\w{6})");

		$item_number = "025000abcdef";
		$quantity = 0;
		$this->obj->parse_barcode_fields($quantity, $item_number);

		echo $quantity;
		$this->assertEquals($quantity, 1);
		$this->assertEquals($item_number, "025000123456");
	}

	public function test_barcode_weight_last()
	{
		$config = $this->getMockBuilder('CI_Config')
			->disableOriginalConstructor()
			->setMethods(['item'])
			->getMock();
		$config->method('item')
			->willReturn("02(\w{6})(\d{5})");

		$item_number = "0212345650001";
		$quantity = 0;
		$this->obj->parse_barcode_fields($quantity, $item_number);

		$this->assertEquals($quantity, 5.001);
		$this->assertEquals($item_number, 123456);
	}
}