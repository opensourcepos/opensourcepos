<?php

namespace App\Models\Tokens;

use app\Models\Appconfig;

/**
 * Token_invoice_sequence class
 *
 * @property appconfig appconfig
 */

class Token_invoice_sequence extends Token
{
	public function __construct(string $value = '')
	{
		parent::__construct($value);
	}

	public function token_id(): string
	{
		return 'ISEQ';
	}

	public function get_value(): string
	{
		return $this->appconfig->acquire_save_next_invoice_sequence();
	}
}
?>