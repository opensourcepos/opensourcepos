<?php

namespace App\Models\Tokens;

/**
 * Token_barcode_price class
 */
class Token_barcode_ean extends Token
{
    public function token_id(): string
	{
        return 'I';
    }

    public function get_value(): string
	{
        return '\w';
    }
}