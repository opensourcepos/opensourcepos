<?php

namespace App\Controllers;

require_once("Secure_Controller.php");

class Office extends Secure_Controller
{
	function __construct()
	{
		parent::__construct('office', NULL, 'office');
	}

	public function index()
	{
		echo view('home/office');
	}

	public function logout()
	{
		$this->Employee->logout();
	}
}
?>
