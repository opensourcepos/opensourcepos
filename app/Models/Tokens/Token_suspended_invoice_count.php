<?php

namespace App\Models\Tokens;

use CodeIgniter\Model;

/**
 * Token_suspended_invoice_count class
 */

class Token_suspended_invoice_count extends Token
{
	public function __construct()
	{
		parent::__construct();

		$this->CI->sale = model('Sale');
	}

	public function token_id()
	{
		return 'SCO';
	}

	public function get_value()
	{
		return $this->CI->Sale->get_suspended_invoice_count();
	}
}
?>
