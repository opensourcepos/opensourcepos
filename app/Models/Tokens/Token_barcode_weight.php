<?php

namespace App\Models\Tokens;

/**
 * Token_barcode_price class
 */
class Token_barcode_weight extends Token
{
    public function token_id(): string
    {
        return 'W';
    }

    public function get_value(): string
    {
        return '\d';
    }
}