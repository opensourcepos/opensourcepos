<?php

namespace App\Models\Tokens;

/**
 * Token_barcode_price class
 */
class Token_barcode_weight extends Token
{
    /**
     * @return string
     */
    public function token_id(): string
    {
        return 'W';
    }

    /**
     * @return string
     */
    public function get_value(): string
    {
        return '\d';
    }
}
