<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Item_kits extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('item_kits');
	}
	
	/*
	Add the total cost and retail price to a passed items kit retrieving the data from each singular item part of the kit
	*/
	private function _add_totals_to_item_kit($item_kit)
	{
		$item_kit->total_cost_price = 0;
		$item_kit->total_unit_price = 0;
		
		foreach($this->Item_kit_items->get_info($item_kit->item_kit_id) as $item_kit_item)
		{
			$item_info = $this->Item->get_info($item_kit_item['item_id']);
			foreach(get_object_vars($item_info) as $property => $value)
			{
				$item_info->$property = $this->xss_clean($value);
			}
			
			$item_kit->total_cost_price += $item_info->cost_price * $item_kit_item['quantity'];
			$item_kit->total_unit_price += $item_info->unit_price * $item_kit_item['quantity'];
		}

		return $item_kit;
	}
	
	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_item_kits_manage_table_headers());

		$this->load->view('item_kits/manage', $data);
	}

	/*
	Returns Item kits table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$item_kits = $this->Item_kit->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Item_kit->get_found_rows($search);

		$data_rows = array();
		foreach($item_kits->result() as $item_kit)
		{
			// calculate the total cost and retail price of the Kit so it can be printed out in the manage table
			$item_kit = $this->_add_totals_to_item_kit($item_kit);
			$data_rows[] = get_item_kit_data_row($item_kit, $this);
		}

		$data_rows = $this->xss_clean($data_rows);

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Item_kit->get_search_suggestions($this->input->post('term')));

		echo json_encode($suggestions);
	}

	public function get_row($row_id)
	{
		// calculate the total cost and retail price of the Kit so it can be added to the table refresh
		$item_kit = $this->_add_totals_to_item_kit($this->Item_kit->get_info($row_id));
		
		echo json_encode(get_item_kit_data_row($item_kit, $this));
	}
	
	public function view($item_kit_id = -1)
	{
		$info = $this->Item_kit->get_info($item_kit_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}
		$data['item_kit_info']  = $info;
		
		$items = array();
		foreach($this->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			$item['name'] = $this->xss_clean($this->Item->get_info($item_kit_item['item_id'])->name);
			$item['item_id'] = $this->xss_clean($item_kit_item['item_id']);
			$item['quantity'] = $this->xss_clean($item_kit_item['quantity']);
			
			$items[] = $item;
		}
		$data['item_kit_items'] = $items;

		$this->load->view("item_kits/form", $data);
	}
	
	public function save($item_kit_id = -1)
	{
		$item_kit_data = array(
			'name' => $this->input->post('name'),
			'description' => $this->input->post('description')
		);
		
		if($this->Item_kit->save($item_kit_data, $item_kit_id))
		{
			$success = TRUE;
			//New item kit
			if ($item_kit_id == -1)
			{
				$item_kit_id = $item_kit_data['item_kit_id'];
			}

			if($this->input->post('item_kit_item') != NULL)
			{
				$item_kit_items = array();
				foreach($this->input->post('item_kit_item') as $item_id => $quantity)
				{
					$item_kit_items[] = array(
						'item_id' => $item_id,
						'quantity' => $quantity
					);
				}

				$success = $this->Item_kit_items->save($item_kit_items, $item_kit_id);
			}

			$item_kit_data = $this->xss_clean($item_kit_data);

			echo json_encode(array('success' => $success,
								'message' => $this->lang->line('item_kits_successful_adding').' '.$item_kit_data['name'], 'id' => $item_kit_id));
		}
		else//failure
		{
			$item_kit_data = $this->xss_clean($item_kit_data);

			echo json_encode(array('success' => FALSE, 
								'message' => $this->lang->line('item_kits_error_adding_updating').' '.$item_kit_data['name'], 'id' => -1));
		}
	}
	
	public function delete()
	{
		$item_kits_to_delete = $this->xss_clean($this->input->post('ids'));

		if($this->Item_kit->delete_list($item_kits_to_delete))
		{
			echo json_encode(array('success' => TRUE,
								'message' => $this->lang->line('item_kits_successful_deleted').' '.count($item_kits_to_delete).' '.$this->lang->line('item_kits_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE,
								'message' => $this->lang->line('item_kits_cannot_be_deleted')));
		}
	}
	
	public function generate_barcodes($item_kit_ids)
	{
		$this->load->library('barcode_lib');
		$result = array();

		$item_kit_ids = explode(':', $item_kit_ids);
		foreach($item_kit_ids as $item_kid_id)
		{		
			// calculate the total cost and retail price of the Kit so it can be added to the barcode text at the bottom
			$item_kit = $this->_add_totals_to_item_kit($this->Item_kit->get_info($item_kid_id));
			
			$item_kid_id = 'KIT '. urldecode($item_kid_id);

			$result[] = array('name' => $item_kit->name, 'item_id' => $item_kid_id, 'item_number' => $item_kid_id,
							'cost_price' => $item_kit->total_cost_price, 'unit_price' => $item_kit->total_unit_price);
		}

		$data['items'] = $result;
        $barcode_config = $this->barcode_lib->get_barcode_config();
		// in case the selected barcode type is not Code39 or Code128 we set by default Code128
		// the rationale for this is that EAN codes cannot have strings as seed, so 'KIT ' is not allowed
		if($barcode_config['barcode_type'] != 'Code39' && $barcode_config['barcode_type'] != 'Code128')
		{
			$barcode_config['barcode_type'] = 'Code128';
		}
		$data['barcode_config'] = $barcode_config;

		// display barcodes
		$this->load->view("barcodes/barcode_sheet", $data);
	}
}
?>