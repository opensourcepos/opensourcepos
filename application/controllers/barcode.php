<?php
class Barcode extends CI_Controller 
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