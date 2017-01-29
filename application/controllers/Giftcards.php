<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Giftcards extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('giftcards');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_giftcards_manage_table_headers());

		$this->load->view('giftcards/manage', $data);
	}

	/*
	Returns Giftcards table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$giftcards = $this->Giftcard->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Giftcard->get_found_rows($search);

		$data_rows = array();
		foreach($giftcards->result() as $giftcard)
		{
			$data_rows[] = get_giftcard_data_row($giftcard, $this);
		}

		$data_rows = $this->xss_clean($data_rows);

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Giftcard->get_search_suggestions($this->input->post('term')));

		echo json_encode($suggestions);
	}

	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_giftcard_data_row($this->Giftcard->get_info($row_id), $this));

		echo json_encode($data_row);
	}

	public function view($giftcard_id = -1)
	{
		$giftcard_info = $this->Giftcard->get_info($giftcard_id);

		$data['selected_person_name'] = ($giftcard_id > 0 && isset($giftcard_info->person_id)) ? $giftcard_info->first_name . ' ' . $giftcard_info->last_name : '';
		$data['selected_person_id']   = $giftcard_info->person_id;
		$data['giftcard_number']      = $giftcard_id > 0 ? $giftcard_info->giftcard_number : $this->Giftcard->get_max_number()->giftcard_number + 1;
		$data['giftcard_id']          = $giftcard_id;
		$data['giftcard_value']       = $giftcard_info->value;

		$data = $this->xss_clean($data);

		$this->load->view("giftcards/form", $data);
	}
	
	public function save($giftcard_id = -1)
	{
		$giftcard_data = array(
			'record_time' => date('Y-m-d H:i:s'),
			'giftcard_number' => $this->input->post('giftcard_number'),
			'value' => parse_decimals($this->input->post('value')),
			'person_id' => $this->input->post('person_id') == '' ? NULL : $this->input->post('person_id')
		);

		if($this->Giftcard->save($giftcard_data, $giftcard_id))
		{
			$giftcard_data = $this->xss_clean($giftcard_data);
			
			//New giftcard
			if($giftcard_id == -1)
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('giftcards_successful_adding').' '.
								$giftcard_data['giftcard_number'], 'id' => $giftcard_data['giftcard_id']));
			}
			else //Existing giftcard
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('giftcards_successful_updating').' '.
								$giftcard_data['giftcard_number'], 'id' => $giftcard_id));
			}
		}
		else //failure
		{
			$giftcard_data = $this->xss_clean($giftcard_data);
			
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('giftcards_error_adding_updating').' '.
							$giftcard_data['giftcard_number'], 'id' => -1));
		}
	}

	public function delete()
	{
		$giftcards_to_delete = $this->xss_clean($this->input->post('ids'));

		if($this->Giftcard->delete_list($giftcards_to_delete))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('giftcards_successful_deleted').' '.
							count($giftcards_to_delete).' '.$this->lang->line('giftcards_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('giftcards_cannot_be_deleted')));
		}
	}
}
?>
