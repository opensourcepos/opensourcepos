<?php
require_once ("Secure_area.php");
class Barcode extends Secure_area 
{
	function __construct()
	{
		parent::__construct();	
	}
	
	function index()
	{		
		$this->load->view('barcode');
	}	
	
}
?>