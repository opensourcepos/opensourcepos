<?php
/*
This interface is implemented by any controller that keeps track of people, such
as customers and employees.
*/
require_once("idata_controller.php");
interface iPerson_controller extends iData_controller
{
	public function mailto();
}
?>