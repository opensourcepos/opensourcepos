<?php

namespace App\Models\Tokens;

use CodeIgniter\Model;

/**
 * Token_invoice_sequence class
 */

class Token_invoice_sequence extends Token
{

	public function __construct($value = '')
	{
		parent::__construct($value);
	}

	public function token_id()
	{
		return 'ISEQ';
	}

	public function get_value()
	{
		return $this->CI->Appconfig->acquire_save_next_invoice_sequence();
	}
}
?>
