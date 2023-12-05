<?php

namespace App\Models\Tokens;

/**
 * Token_barcode_price class
 */
class Token_barcode_ean extends Token
{
	/**
	 * @return string
	 */
	public function token_id(): string
	{
        return 'I';
    }

	/**
	 * @return string
	 */
	public function get_value(): string
	{
        return '\w';
    }
}
