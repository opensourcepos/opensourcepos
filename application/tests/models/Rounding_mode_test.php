<?php
/**
 * @backupGlobals disabled
 */
class Rounding_mode_test extends UnitTestCase
{
    public function test_rounding()
    {
        $this->assertEquals(5.20, round_number(Rounding_mode::HALF_FIVE, 5.20, 2));
        $this->assertEquals(5.20, round_number(Rounding_mode::HALF_FIVE, 5.21, 2));
        $this->assertEquals(5.20, round_number(Rounding_mode::HALF_FIVE, 5.22, 2));
        $this->assertEquals(5.25, round_number(Rounding_mode::HALF_FIVE, 5.23, 2));
        $this->assertEquals(5.25, round_number(Rounding_mode::HALF_FIVE, 5.24, 2));
        $this->assertEquals(5.25, round_number(Rounding_mode::HALF_FIVE, 5.25, 2));
        $this->assertEquals(5.25, round_number(Rounding_mode::HALF_FIVE, 5.26, 2));
        $this->assertEquals(5.25, round_number(Rounding_mode::HALF_FIVE, 5.27, 2));
        $this->assertEquals(5.30, round_number(Rounding_mode::HALF_FIVE, 5.28, 2));
        $this->assertEquals(5.30, round_number(Rounding_mode::HALF_FIVE, 5.29, 2));
    }
}
