<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");

class Item_kits extends Secure_area implements iData_controller
{
	function __construct()
	{
		parent::__construct('item_kits');
	}
	
	// add the total cost and retail price to a passed items kit retrieving the data from each singolar item part of the kit
	private function add_totals_to_item_kit($item_kit)
	{
		$item_kit->total_cost_price = 0;
		$item_kit->total_unit_price = 0;
		
		foreach ($this->Item_kit_items->get_info($item_kit->item_kit_id) as $item_kit_item)
		{
			$item_info = $this->Item->get_info($item_kit_item['item_id']);
			
			$item_kit->total_cost_price += $item_info->cost_price * $item_kit_item['quantity'];
			$item_kit->total_unit_price += $item_info->unit_price * $item_kit_item['quantity'];
		}

		return $item_kit;
	}
	
	function index()
	{
		$data['controller_name'] = $this->get_controller_name();
		$data['table_headers'] = get_suppliers_manage_table_headers();

		$this->load->view('item_kits/manage', $data);
	}

	/*
	Returns Item kits table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$lines_per_page = $this->Appconfig->get('lines_per_page');

		$item_kits = $this->Item_kit->search($search, $offset, $limit);
		$total_rows = $this->Item_kit->get_found_rows($search);
		//$links = $this->_initialize_pagination($this->Item_kit, $lines_per_page, $limit, $total_rows, 'search');
		$data_rows = array();
		foreach($item_kits->result() as $item_kit)
		{
			// calculate the total cost and retail price of the Kit so it can be printed out in the manage table
			$item_kit = $this->add_totals_to_item_kit($item_kit);
			$data_rows = get_item_kit_data_row($item_kits, $this);
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	function suggest_search()
	{
		$suggestions = $this->Item_kit->get_search_suggestions($this->input->post('term'));
		echo json_encode($suggestions);
	}

	function get_row($row_id)
	{
		// calculate the total cost and retail price of the Kit so it can be added to the table refresh
		$item_kit = $this->add_totals_to_item_kit($this->Item_kit->get_info($item_kit_id));
		
		echo json_encode(get_item_kit_data_row($item_kit, $this));
	}

	function view($item_kit_id=-1)
	{
		$data['item_kit_info'] = $this->Item_kit->get_info($item_kit_id);
		$this->load->view("item_kits/form", $data);
	}
	
	function save($item_kit_id=-1)
	{
		$item_kit_data = array(
			'name' => $this->input->post('name'),
			'description' => $this->input->post('description')
		);
		
		if ($this->Item_kit->save($item_kit_data, $item_kit_id))
		{
			//New item kit
			if ($item_kit_id==-1)
			{
				$item_kit_id = $item_kit_data['item_kit_id'];
				
				echo json_encode(array('success'=>true,
									'message'=>$this->lang->line('item_kits_successful_adding').' '.$item_kit_data['name'],
									'id'=>$item_kit_id));
			}
			else //previous item
			{
				echo json_encode(array('success'=>true, 
									'message'=>$this->lang->line('item_kits_successful_updating').' '.$item_kit_data['name'],
									'id'=>$item_kit_id));
			}
			
			if ( $this->input->post('item_kit_item') != null )
			{
				$item_kit_items = array();
				foreach($this->input->post('item_kit_item') as $item_id => $quantity)
				{
					$item_kit_items[] = array(
						'item_id' => $item_id,
						'quantity' => $quantity
					);
				}
			
				$this->Item_kit_items->save($item_kit_items, $item_kit_id);
			}
		}
		else//failure
		{
			echo json_encode(array('success'=>false, 
								'message'=>$this->lang->line('item_kits_error_adding_updating').' '.$item_kit_data['name'],
								'id'=>-1));
		}
	}
	
	function delete()
	{
		$item_kits_to_delete = $this->input->post('ids');

		if ($this->Item_kit->delete_list($item_kits_to_delete))
		{
			echo json_encode(array('success'=>true,
								'message'=>$this->lang->line('item_kits_successful_deleted').' '.count($item_kits_to_delete).' '.$this->lang->line('item_kits_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,
								'message'=>$this->lang->line('item_kits_cannot_be_deleted')));
		}
	}
	
	function generate_barcodes($item_kit_ids)
	{
		$this->load->library('barcode_lib');
		$result = array();

		$item_kit_ids = explode(':', $item_kit_ids);
		foreach ($item_kit_ids as $item_kid_id)
		{		
			// calculate the total cost and retail price of the Kit so it can be added to the barcode text at the bottom
			$item_kit = $this->add_totals_to_item_kit($this->Item_kit->get_info($item_kid_id));

			$result[] = array('name'=>$item_kit->name, 'item_id'=>'KIT '.$item_kid_id, 'item_number'=>'KIT '.$item_kid_id, 'cost_price'=>$item_kit->total_cost_price, 'unit_price'=>$item_kit->total_unit_price);
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