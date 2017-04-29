<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

		$this->load->view('people/manage', $data);
	}

	/*
	 Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Person->get_search_suggestions($this->input->post('term')));

		echo json_encode($suggestions);
	}

	/*
	Gets one row for a person manage table. This is called using AJAX to update one row.
	*/
	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_person_data_row($this->Person->get_info($row_id), $this));

		echo json_encode($data_row);
	}

	/*
	Capitalize segments of a name, and put the rest into lower case. You can pass the characters you want to use as delimiters.

	i.e. <?php echo nameize("john o'grady-smith"); ?>

	returns John O'Grady-Smith
	*/
	protected function nameize($str, $a_char = array("'", "-", " "))
	{	
		// $str contains the complete raw name string
		// $a_char is an array containing the characters we use as separators for capitalization. If you don't pass anything, there are three in there as default.
		$string = strtolower($str);

		foreach($a_char as $temp)
		{
			$pos = strpos($string, $temp);
			if($pos)
			{
				// we are in the loop because we found one of the special characters in the array, so lets split it up into chunks and capitalize each one.
				$mend = '';
				$a_split = explode($temp, $string);
				foreach($a_split as $temp2)
				{
					// capitalize each portion of the string which was separated at a special character
					$mend .= ucfirst($temp2).$temp;
				}
				$string = substr($mend, 0, -1);
			}			
		}

		return ucfirst($string);
	}
}
?>
