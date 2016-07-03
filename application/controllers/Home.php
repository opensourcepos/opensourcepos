<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Home extends Secure_Controller 
{
	public function __construct()
	{
		parent::__construct();	
	}
	
	public function index()
	{
		$this->load->view('home');
	}
	
	public function logout()
	{
		$this->Employee->logout();
	}
}
?>