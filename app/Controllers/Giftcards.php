<?php

namespace App\Controllers;

use app\Models\Appconfig;
use app\Models\Giftcard;

/**
 *
 *
 * @property appconfig appconfig
 * @property giftcard giftcard
 *
 */
class Giftcards extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('giftcards');

		$this->appconfig = model('Appconfig');
		$this->giftcard = model('Giftcard');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_giftcards_manage_table_headers());

		echo view('giftcards/manage', $data);
	}

	/*
	Returns Giftcards table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->request->getGet('search');
		$limit  = $this->request->getGet('limit');
		$offset = $this->request->getGet('offset');
		$sort   = $this->request->getGet('sort');
		$order  = $this->request->getGet('order');

		$giftcards = $this->giftcard->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->giftcard->get_found_rows($search);

		$data_rows = [];
		foreach($giftcards->getResult() as $giftcard)
		{
			$data_rows[] = $this->xss_clean(get_giftcard_data_row($giftcard));
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/

	public function suggest()
	{
		$suggestions = $this->xss_clean($this->giftcard->get_search_suggestions($this->request->getGet('term'), TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->giftcard->get_search_suggestions($this->request->getPost('term')));

		echo json_encode($suggestions);
	}

	public function get_row(int $row_id)
	{
		$data_row = $this->xss_clean(get_giftcard_data_row($this->giftcard->get_info($row_id)));

		echo json_encode($data_row);
	}

	public function view(int $giftcard_id = -1)	//TODO: Need to replace -1 with a constant
	{
		$giftcard_info = $this->giftcard->get_info($giftcard_id);

		$data['selected_person_name'] = ($giftcard_id > 0 && isset($giftcard_info->person_id)) ? $giftcard_info->first_name . ' ' . $giftcard_info->last_name : '';
		$data['selected_person_id'] = $giftcard_info->person_id;
		if($this->appconfig->get('giftcard_number') == "random")
		{
			$data['giftcard_number'] = $giftcard_id > 0 ? $giftcard_info->giftcard_number : '';
		}
		else
		{
			$max_giftnumber = isset($this->giftcard->get_max_number()->giftcard_number) ? $this->Giftcard->get_max_number()->giftcard_number : 0;	//TODO: variable does not follow naming standard.
			$data['giftcard_number'] = $giftcard_id > 0 ? $giftcard_info->giftcard_number : $max_giftnumber + 1;
		}
		$data['giftcard_id'] = $giftcard_id;
		$data['giftcard_value'] = $giftcard_info->value;

		$data = $this->xss_clean($data);

		echo view("giftcards/form", $data);
	}

	public function save(int $giftcard_id = -1)	//TODO: Replace -1 with a constant
	{
		$giftcard_number = $this->request->getPost('giftcard_number');

		if($giftcard_id == -1 && trim($giftcard_number) == '')
		{
			$giftcard_number = $this->giftcard->generate_unique_giftcard_name($this->request->getPost('giftcard_amount'));
		}

		$giftcard_data = [
			'record_time' => date('Y-m-d H:i:s'),
			'giftcard_number' => $giftcard_number,
			'value' => parse_decimals($this->request->getPost('giftcard_amount')),
			'person_id' => $this->request->getPost('person_id') == '' ? NULL : $this->request->getPost('person_id')
		];

		if($this->giftcard->save($giftcard_data, $giftcard_id))	//TODO: Reflection exception
		{
			$giftcard_data = $this->xss_clean($giftcard_data);

			//New giftcard
			if($giftcard_id == -1)	//TODO: Constant needed
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Giftcards.successful_adding') . ' ' . $giftcard_data['giftcard_number'],
					'id' => $giftcard_data['giftcard_id']
				]);
			}
			else //Existing giftcard
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Giftcards.successful_updating') . ' ' . $giftcard_data['giftcard_number'],
					'id' => $giftcard_id
				]);
			}
		}
		else //failure
		{
			$giftcard_data = $this->xss_clean($giftcard_data);
			
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Giftcards.error_adding_updating') . ' ' . $giftcard_data['giftcard_number'],
				'id' => -1
			]);
		}
	}

	public function ajax_check_number_giftcard()
	{
		$parsed_value = parse_decimals($this->request->getPost('giftcard_amount'));
		echo json_encode (['success' => $parsed_value !== FALSE, 'giftcard_amount' => to_currency_no_money($parsed_value)]);
	}

	public function delete()
	{
		$giftcards_to_delete = $this->xss_clean($this->request->getPost('ids'));

		if($this->giftcard->delete_list($giftcards_to_delete))
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Giftcards.successful_deleted') . ' ' . count($giftcards_to_delete).' '.lang('Giftcards.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Giftcards.cannot_be_deleted')]);
		}
	}
}
?>
