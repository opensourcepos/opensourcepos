<?php

namespace App\Controllers;

use App\Models\Giftcard;

/**
 * @property giftcard giftcard
 */
class Giftcards extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('giftcards');

		$this->giftcard = model('Giftcard');
	}

	public function getIndex(): void
	{
		$data['table_headers'] = get_giftcards_manage_table_headers();

		echo view('giftcards/manage', $data);
	}

	/*
	Returns Giftcards table data rows. This will be called with AJAX.
	*/
	public function getSearch(): void
	{
		$search = $this->request->getVar('search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$limit  = $this->request->getVar('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getVar('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort   = $this->request->getVar('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order  = $this->request->getVar('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$giftcards = $this->giftcard->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->giftcard->get_found_rows($search);

		$data_rows = [];
		foreach($giftcards->getResult() as $giftcard)
		{
			$data_rows[] = get_giftcard_data_row($giftcard);
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/

	public function getSuggest(): void
	{
		$suggestions = $this->giftcard->get_search_suggestions($this->request->getVar('term', FILTER_SANITIZE_FULL_SPECIAL_CHARS), TRUE);

		echo json_encode($suggestions);
	}

	public function suggest_search(): void
	{
		$suggestions = $this->giftcard->get_search_suggestions($this->request->getPost('term', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

		echo json_encode($suggestions);
	}

	public function getRow(int $row_id): void
	{
		$data_row = get_giftcard_data_row($this->giftcard->get_info($row_id));

		echo json_encode($data_row);
	}

	public function getView(int $giftcard_id = NEW_ENTRY): void
	{
		$config = config(OSPOS::class)->settings;
		$giftcard_info = $this->giftcard->get_info($giftcard_id);

		$data['selected_person_name'] = ($giftcard_id > 0 && isset($giftcard_info->person_id)) ? $giftcard_info->first_name . ' ' . $giftcard_info->last_name : '';
		$data['selected_person_id'] = $giftcard_info->person_id;
		if($config['giftcard_number'] == 'random')
		{
			$data['giftcard_number'] = $giftcard_id > 0 ? $giftcard_info->giftcard_number : '';
		}
		else
		{
			$max_number_obj = $this->giftcard->get_max_number();
			$max_giftnumber = isset($max_number_obj) ? $this->giftcard->get_max_number()->giftcard_number : 0;	//TODO: variable does not follow naming standard.
			$data['giftcard_number'] = $giftcard_id > 0 ? $giftcard_info->giftcard_number : $max_giftnumber + 1;
		}
		$data['giftcard_id'] = $giftcard_id;
		$data['giftcard_value'] = $giftcard_info->value;

		echo view("giftcards/form", $data);
	}

	public function postSave(int $giftcard_id = NEW_ENTRY): void
	{
		$giftcard_number = $this->request->getPost('giftcard_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if($giftcard_id == NEW_ENTRY && trim($giftcard_number) == '')
		{
			$giftcard_number = $this->giftcard->generate_unique_giftcard_name($this->request->getPost('giftcard_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
		}

		$giftcard_data = [
			'record_time' => date('Y-m-d H:i:s'),
			'giftcard_number' => $giftcard_number,
			'value' => parse_decimals($this->request->getPost('giftcard_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
			'person_id' => $this->request->getPost('person_id') == '' ? NULL : $this->request->getPost('person_id', FILTER_SANITIZE_NUMBER_INT)
		];

		if($this->giftcard->save_value($giftcard_data, $giftcard_id))
		{
			//New giftcard
			if($giftcard_id == NEW_ENTRY)	//TODO: Constant needed
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
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Giftcards.error_adding_updating') . ' ' . $giftcard_data['giftcard_number'],
				'id' => NEW_ENTRY
			]);
		}
	}

	/**
	 * Function called in the view to check the giftcard.
	 *
	 * @return void
	 */
	public function postCheckNumberGiftcard(): void
	{
		$parsed_value = parse_decimals($this->request->getPost('giftcard_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
		echo json_encode (['success' => $parsed_value !== FALSE, 'giftcard_amount' => to_currency_no_money($parsed_value)]);
	}

	public function postDelete(): void
	{
		$giftcards_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

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
