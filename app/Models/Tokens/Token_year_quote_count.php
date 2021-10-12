<?php

namespace App\Models\Tokens;

use app\Models\Sale;

/**
 * Token_year_quote_count class
 *
 * @property sale sale
 *
 */
class Token_year_quote_count extends Token
{
	public function __construct()
	{
		parent::__construct();

		$this->sale = model('Sale');
	}

	public function token_id(): string
	{
		return 'QCO';
	}

	public function get_value(): int
	{
		return $this->sale->get_quote_number_for_year();
	}
}
?>
