<?php
/**
 * @backupGlobals disabled
 */


class Rounding_mode_test extends UnitTestCase
{

    public function setUp()
    {
        $this->resetInstance();
        $this->CI->load->model('enums/Rounding_mode');
    }

    public function test_rounding()
    {
        // $this->assertEquals(5.20, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.20, 2));
        $this->assertEquals(5.20, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.20, 2));
        $this->assertEquals(5.20, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.21, 2));
        $this->assertEquals(5.20, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.22, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.23, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.24, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.25, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.26, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.27, 2));
        $this->assertEquals(5.30, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.28, 2));
        $this->assertEquals(5.30, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.29, 2));
    }
}

