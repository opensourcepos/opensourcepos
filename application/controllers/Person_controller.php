<?php
require_once ("Secure_area.php");
abstract class Person_controller extends Secure_area
{
	function __construct($module_id=null)
	{
		parent::__construct($module_id);		
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Person->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
		
	/*
	Gets one row for a person manage table. This is called using AJAX to update one row.
	*/
	function get_row($row_id)
	{
		$data_row=get_person_data_row($this->Person->get_info($row_id),$this);
		echo json_encode($data_row);
	}
}
?>