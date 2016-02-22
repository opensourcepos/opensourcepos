<?php
require_once ("secure_area.php");

class Home extends Secure_area {
	function __construct() {
		parent::__construct();	
	}
	
	function index() {
		redirect('reports');
    $this->smartyci->useCached( 'home.php.tpl' );
    $this->smartyci->display( 'home.php.tpl' );
	}
	
	function logout() {
		$this->Employee->logout();
	}
}
?>