<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_barcode_price class
 */

class Token_barcode_ean extends Token
{

    public function token_id()
    {
        return 'I';
    }

    public function get_value()
    {
        return '\w';
    }

}

?>