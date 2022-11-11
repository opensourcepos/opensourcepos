<?php
/**
 * @backupGlobals disabled
 */
class Locale_helper_test extends UnitTestCase
{
    public function test_parse_decimals_precision()
    {
        $decimals = parse_decimals(2.22, 2);

        $this->assertEquals(2.22, $decimals);
    }

    public function test_format_decimals()
    {
        $decimals = to_decimals(5, 2);

        $this->assertEquals(5.00, $decimals);
    }


}
