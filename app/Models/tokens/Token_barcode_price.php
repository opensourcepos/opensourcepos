<?php

namespace App\Models\tokens;

use CodeIgniter\Model;

/**
 * Token_barcode_price class
 */

class Token_barcode_price extends Token
{

    public function token_id()
    {
        return 'P';
    }

    public function get_value()
    {
        return '\d';
    }

}

?>