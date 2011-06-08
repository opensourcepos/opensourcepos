<?php
require_once ("interfaces/iperson_controller.php");
require_once ("secure_area.php");
abstract class Person_controller extends Secure_area implements iPerson_controller
{
	function __construct($module_id=null)
	{
		parent::__construct($module_id);		
	}
	
	/*
	This returns a mailto link for persons with a certain id. This is called with AJAX.
	*/
	function mailto()
	{
		$people_to_email=$this->input->post('ids');
		
		if($people_to_email!=false)
		{
			$mailto_url='mailto:';
			foreach($this->Person->get_multiple_info($people_to_email)->result() as $person)
			{
				$mailto_url.=$person->email.',';	
			}
			//remove last comma
			$mailto_url=substr($mailto_url,0,strlen($mailto_url)-1);
			
			echo $mailto_url;
			exit;
		}
		echo '#';
	}
	
	/*
	Gets one row for a person manage table. This is called using AJAX to update one row.
	*/
	function get_row()
	{
		$person_id = $this->input->post('row_id');
		$data_row=get_person_data_row($this->Person->get_info($person_id),$this);
		echo $data_row;
	}
		
}
?>