<?php

namespace App\Models\Tokens;

use CodeIgniter\Model;

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