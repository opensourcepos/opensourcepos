<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");

class Giftcards extends Secure_area implements iData_controller
{
	function __construct()
	{
		parent::__construct('giftcards');
	}

	function index($limit_from=0)
	{
		$data['controller_name'] = $this->get_controller_name();
		$data['form_width'] = $this->get_form_width();
		$lines_per_page = $this->Appconfig->get('lines_per_page');
		$giftcards = $this->Giftcard->get_all($lines_per_page, $limit_from);
		$data['links'] = $this->_initialize_pagination($this->Giftcard, $lines_per_page, $limit_from);
		$data['manage_table'] = get_giftcards_manage_table($giftcards, $this);
		$this->load->view('giftcards/manage', $data);
	}

	function search()
	{
		$search = $this->input->post('search');
		$limit_from = $this->input->post('limit_from');
		$lines_per_page = $this->Appconfig->get('lines_per_page');
		$giftcards = $this->Giftcard->search($search, $lines_per_page, $limit_from);
		$total_rows = $this->Giftcard->get_found_rows($search);
		$links = $this->_initialize_pagination($this->Giftcard, $lines_per_page, $limit_from, $total_rows);
		$data_rows = get_giftcards_manage_table_data_rows($giftcards, $this);
		echo json_encode(array('total_rows' => $total_rows, 'rows' => $data_rows, 'pagination' => $links));
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Giftcard->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	/*
	 Gives search suggestions for person_id based on what is being searched for
	*/
	function person_search()
	{
		$suggestions = $this->Customer->get_customer_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function get_row()
	{
		$giftcard_id = $this->input->post('row_id');
		$data_row = get_giftcard_data_row($this->Giftcard->get_info($giftcard_id), $this);
		echo $data_row;
	}

	function view($giftcard_id=-1)
	{
		$giftcard_info = $this->Giftcard->get_info($giftcard_id);
		$person_name=$giftcard_id > 0? $giftcard_info->first_name . ' ' . $giftcard_info->last_name : '';
		$data['selected_person'] = $giftcard_id > 0 && isset($giftcard_info->person_id) ? $giftcard_info->person_id . "|" . $person_name : "";
		$data['giftcard_number'] = $giftcard_id > 0 ? $giftcard_info->giftcard_number : $this->Giftcard->get_max_number()->giftcard_number + 1;
		$data['giftcard_info'] = $giftcard_info;
		$this->load->view("giftcards/form",$data);
	}
	
	function save($giftcard_id=-1)
	{
		$giftcard_data = array(
			'record_time' => date('Y-m-d H:i:s'),
			'giftcard_number'=>$this->input->post('giftcard_number', TRUE),
			'value'=>$this->input->post('value', TRUE),
			'person_id'=>$this->input->post('person_id', TRUE) ? $this->input->post('person_id') : NULL		
		);

		if( $this->Giftcard->save( $giftcard_data, $giftcard_id ) )
		{
			//New giftcard
			if($giftcard_id==-1)
			{
				echo json_encode(array('success'=>true, 'message'=>$this->lang->line('giftcards_successful_adding').' '.
								$giftcard_data['giftcard_number'], 'giftcard_id'=>$giftcard_data['giftcard_id']));
				$giftcard_id = $giftcard_data['giftcard_id'];
			}
			else //previous giftcard
			{
				echo json_encode(array('success'=>true, 'message'=>$this->lang->line('giftcards_successful_updating').' '.
								$giftcard_data['giftcard_number'], 'giftcard_id'=>$giftcard_id));
			}
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('giftcards_error_adding_updating').' '.
							$giftcard_data['giftcard_number'], 'giftcard_id'=>-1));
		}
	}

	function delete()
	{
		$giftcards_to_delete=$this->input->post('ids');

		if($this->Giftcard->delete_list($giftcards_to_delete))
		{
			echo json_encode(array('success'=>true, 'message'=>$this->lang->line('giftcards_successful_deleted').' '.
							count($giftcards_to_delete).' '.$this->lang->line('giftcards_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false, 'message'=>$this->lang->line('giftcards_cannot_be_deleted')));
		}
	}
		
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{
		return 360;
	}
}
?>
