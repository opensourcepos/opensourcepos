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
		$kit_item_info = $this->Item->get_info(isset($item_kit->kit_item_id) ? $item_kit->kit_item_id : $item_kit->item_id);

		$item_kit->total_cost_price = 0;
		$item_kit->total_unit_price = (float)$kit_item_info->unit_price;
		$total_quantity = 0;

		foreach($this->Item_kit_items->get_info($item_kit->item_kit_id) as $item_kit_item)
		{
			$item_info = $this->Item->get_info($item_kit_item['item_id']);
			foreach(get_object_vars($item_info) as $property => $value)
			{
				$item_info->$property = $this->xss_clean($value);
			}

			$item_kit->total_cost_price += $item_info->cost_price * $item_kit_item['quantity'];

			if($item_kit->price_option == PRICE_OPTION_ALL || ($item_kit->price_option == PRICE_OPTION_KIT_STOCK && $item_info->stock_type == HAS_STOCK ))
			{
				$item_kit->total_unit_price += $item_info->unit_price * $item_kit_item['quantity'];
				$total_quantity += $item_kit_item['quantity'];
			}
		}

		$discount_fraction = bcdiv($item_kit->kit_discount, 100);

		$item_kit->total_unit_price = $item_kit->total_unit_price - round(($item_kit->kit_discount_type == PERCENT)?bcmul($item_kit->total_unit_price, $discount_fraction): $item_kit->kit_discount, totals_decimals(), PHP_ROUND_HALF_UP);

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
			$data_rows[] = $this->xss_clean(get_item_kit_data_row($item_kit));
		}

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

		echo json_encode(get_item_kit_data_row($item_kit));
	}
	
	public function view($item_kit_id = -1)
	{
		$info = $this->Item_kit->get_info($item_kit_id);

		if($item_kit_id == -1)
		{
			$info->price_option = '0';
			$info->print_option = PRINT_ALL;
			$info->kit_item_id = 0;
		}
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}

		$data['item_kit_info']  = $info;

		$items = array();
		foreach($this->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			$item['kit_sequence'] = $this->xss_clean($item_kit_item['kit_sequence']);
			$item['name'] = $this->xss_clean($this->Item->get_info($item_kit_item['item_id'])->name);
			$item['item_id'] = $this->xss_clean($item_kit_item['item_id']);
			$item['quantity'] = $this->xss_clean($item_kit_item['quantity']);

			$items[] = $item;
		}

		$data['item_kit_items'] = $items;

		$data['selected_kit_item_id'] = $info->kit_item_id;
		$data['selected_kit_item'] = ($item_kit_id > 0 && isset($info->kit_item_id)) ? $info->item_name : '';

		$this->load->view("item_kits/form", $data);
	}
	
	public function save($item_kit_id = -1)
	{
		$item_kit_data = array(
			'name' => $this->input->post('name'),
			'item_id' => $this->input->post('kit_item_id'),
			'kit_discount' => $this->input->post('kit_discount'),
			'kit_discount_type' => $this->input->post('kit_discount_type') == NULL ? PERCENT : $this->input->post('kit_discount_type'),
			'price_option' => $this->input->post('price_option'),
			'print_option' => $this->input->post('print_option'),
			'description' => $this->input->post('description')
		);
		
		if($this->Item_kit->save($item_kit_data, $item_kit_id))
		{
			$success = TRUE;
			$new_item = FALSE;
			//New item kit
			if($item_kit_id == -1)
			{
				$item_kit_id = $item_kit_data['item_kit_id'];
				$new_item = TRUE;
			}

			if($this->input->post('item_kit_qty') != NULL)
			{
				$item_kit_items = array();
				foreach($this->input->post('item_kit_qty') as $item_id => $quantity)
				{
					$seq = $this->input->post('item_kit_seq[' . $item_id . ']');
					$item_kit_items[] = array(
						'item_id' => $item_id,
						'quantity' => $quantity,
						'kit_sequence' => $seq
					);
				}

			}

			$success = $this->Item_kit_items->save($item_kit_items, $item_kit_id);

			$item_kit_data = $this->xss_clean($item_kit_data);

			if($new_item)
			{
				echo json_encode(array('success' => $success,
					'message' => $this->lang->line('item_kits_successful_adding').' '.$item_kit_data['name'], 'id' => $item_kit_id));

			}
			else
			{
				echo json_encode(array('success' => $success,
					'message' => $this->lang->line('item_kits_successful_updating').' '.$item_kit_data['name'], 'id' => $item_kit_id));
			}
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
