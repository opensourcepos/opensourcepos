<?php

namespace App\Models\Tokens;

use app\Models\Sale;

/**
 * Token_year_invoice_count class
 *
 * @property sale sale
 *
 */
class Token_year_invoice_count extends Token
{
	public function token_id(): string
	{
		return 'YCO';
	}

	public function get_value(): int
	{
		return $this->sale->get_invoice_number_for_year();
	}
}