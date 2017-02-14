<?php

class Token_invoice_sequence extends Token
{
	private $CI;

	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
	}

	public function get_value()
	{
		return $this->CI->Appconfig->acquire_save_next_invoice_sequence();
	}
}