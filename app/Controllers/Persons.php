<?php

namespace App\Controllers;

require_once("Secure_Controller.php");

abstract class Persons extends Secure_Controller
{
	public function __construct($module_id = NULL)
	{
		parent::__construct($module_id);
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_people_manage_table_headers());

		echo view('people/manage', $data);
	}

	/*
	 Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Person->get_search_suggestions($this->request->getPost('term')));

		echo json_encode($suggestions);
	}

	/*
	Gets one row for a person manage table. This is called using AJAX to update one row.
	*/
	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_person_data_row($this->Person->get_info($row_id)));

		echo json_encode($data_row);
	}

	/*
	Capitalize segments of a name, and put the rest into lower case.
	You can pass the characters you want to use as delimiters as exceptions.
	The function supports UTF-8 string.

	Example:
		i.e. <?php echo nameize("john o'grady-smith"); ?>

		returns John O'Grady-Smith
	*/

	protected function nameize($string)
	{
		return str_name_case($string);
	}
}
?>
