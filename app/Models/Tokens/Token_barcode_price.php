<?php

namespace App\Models\Tokens;

/**
 * Token_barcode_price class
 */
class Token_barcode_price extends Token
{
    public function token_id(): string
	{
        return 'P';
    }

    public function get_value(): string
	{
        return '\d';
    }
}