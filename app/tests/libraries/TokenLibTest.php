<?php
namespace App\Libraries;

use app\Models\Appconfig;
use app\Libraries\Token_lib;
use App\Models\Tokens\Token;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @backupGlobals disabled
 * @property appconfig appconfig
 * @property token_lib token_lib
 */
class TokenLibTest extends CIUnitTestCase
{

    public function setUp(): void
    {
        parent::setUp();
		$this->appconfig = model('Appconfig');
        $this->token_lib = new Token_lib();
    }

    public function test_token_parser()
    {
        $tokens = Token::get_barcode_tokens();

        $parsed_tokens = $this->token_lib->parse("0250200000abc", "02{P:2}{W:6}{I:3}", $tokens);

        $this->assertEquals('abc', $parsed_tokens['I']);
        $this->assertEquals('50', $parsed_tokens['P']);
        $this->assertEquals('200000', $parsed_tokens['W']);

    }

    public function test_barcode_item_number_and_quantity()
    {
        $this->appconfig->set('barcode_formats', json_encode (["02{I:5}{W:6}"]));

        $item_number = "0250000123456";
        $quantity = 0;
        $this->token_lib->parse_barcode($quantity, $price, $item_number);

        $this->assertEquals(123.456, $quantity);
        $this->assertEquals("50000", $item_number);
        $this->assertEquals(NULL, $price);
    }

    public function test_barcode_quantity_and_item_number()
    {
        $this->appconfig->set('barcode_formats', json_encode (["02{I:6}{W:5}{P:2}"]));

        $item_number = "021234565000110";
        $quantity = 0;
        $this->token_lib->parse_barcode($quantity, $price, $item_number);


        $this->assertEquals(10, $price);
        $this->assertEquals(123456, $item_number);
        $this->assertEquals(50.001, $quantity);
    }

    public function test_no_barcode_format()
    {
		$this->appconfig->set('barcode_formats', json_encode ([""]));

        $item_number = "021234565000110";
        $quantity = 0;

        $this->token_lib->parse_barcode($quantity, $price, $item_number);

        $this->assertEquals(1, $quantity);
        $this->assertEquals(0, $price);
        $this->assertEquals("021234565000110", $item_number);
    }
}
