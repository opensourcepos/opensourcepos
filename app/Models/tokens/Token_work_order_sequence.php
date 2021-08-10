<?php

namespace App\Models\tokens;

use CodeIgniter\Model;

/**
 * Token_work_order_sequence class
 */

class Token_work_order_sequence extends Token
{
	public function token_id()
	{
		return 'WSEQ';
	}

	public function get_value()
	{
		return $this->CI->Appconfig->acquire_save_next_work_order_sequence();
	}
}
?>
