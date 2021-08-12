<?php

namespace App\Models\Tokens;

use CodeIgniter\Model;

/**
 * Token_quote_sequence class
 */

class Token_quote_sequence extends Token
{
	public function token_id()
	{
		return 'QSEQ';
	}

	public function get_value()
	{
		return $this->CI->Appconfig->acquire_save_next_quote_sequence();
	}
}
?>
