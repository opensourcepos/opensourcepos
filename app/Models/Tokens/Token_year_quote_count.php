<?php

namespace App\Models\Tokens;

use App\Models\Sale;

/**
 * Token_year_quote_count class
 *
 * @property sale sale
 *
 */
class Token_year_quote_count extends Token
{
	public function token_id(): string
	{
		return 'QCO';
	}

	public function get_value(): int
	{
		$sale = model(Sale::class);
		return $sale->get_quote_number_for_year();
	}
}