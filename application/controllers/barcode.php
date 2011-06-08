<?php
class Barcode extends Controller 
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