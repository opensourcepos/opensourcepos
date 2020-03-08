<?php
/**
 * @backupGlobals disabled
 */
class Token_lib_test extends UnitTestCase
{

    public function setUp()
    {
        $this->resetInstance();

        $this->obj = $this->newLibrary('Token_lib');
    }

    public function test_token_parser()
    {
        $tokens = Token::get_barcode_tokens();

        $parsed_tokens = $this->obj->parse("0250200000abc", "02{P:2}{W:6}{I:3}", $tokens);

        $this->assertEquals($parsed_tokens['I'], 'abc');
        $this->assertEquals($parsed_tokens['P'], '50');
        $this->assertEquals($parsed_tokens['W'], '200000');

    }

}

?>