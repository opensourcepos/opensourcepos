<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Token_barcode_price class
 */

class Token_barcode_weight extends Token
{

    public function token_id()
    {
        return 'W';
    }

    public function get_value()
    {
        return '\d';
    }

}

?>
