<?php
require_once ("Secure_area.php");

abstract class Person_controller extends Secure_area
{
	function __construct($module_id = NULL)
	{
		parent::__construct($module_id);		
	}
	
	/*
	 Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->security->xss_clean($this->Person->get_search_suggestions($this->input->post('term')));

		echo json_encode($suggestions);
	}
		
	/*
	Gets one row for a person manage table. This is called using AJAX to update one row.
	*/
	public function get_row($row_id)
	{
		$data_row = $this->security->xss_clean(get_person_data_row($this->Person->get_info($row_id), $this));

		echo json_encode($data_row);
	}
}
?>