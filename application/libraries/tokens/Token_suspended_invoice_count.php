<?php

class Token_suspended_invoice_count extends Token
{
	private $CI;

	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
		$this->CI->load->model('Sale_suspended');
	}

	public function get_value()
	{
		return $this->CI->Sale_suspended->get_invoice_count();
	}
}

?>