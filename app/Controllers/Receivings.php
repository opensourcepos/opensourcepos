<?php

namespace App\Controllers;

use app\Libraries\Receiving_lib;
use app\Libraries\Token_lib;
use app\Libraries\Barcode_lib;
use app\Models\Inventory;
use app\Models\Item;
use app\Models\Item_kit;
use app\Models\Receiving;
use app\Models\Stock_location;
use app\Models\Supplier;

/**
 * @property receiving_lib receiving_lib
 * @property token_lib token_lib
 * @property barcode_lib barcode_lib
 * @property inventory inventory
 * @property item item
 * @property item_kit item_kit
 * @property receiving receiving
 * @property stock_location stock_location
 * @property supplier supplier
 */
class Receivings extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('receivings');

		$this->receiving_lib = new Receiving_lib();
		$this->token_lib = new Token_lib();
		$this->barcode_lib = new Barcode_lib();

		$this->inventory = model('Inventory');
		$this->item_kit = model('Item_kit');
		$this->item = model('Item');
		$this->receiving = model('Receiving');
		$this->stock_location = model('Stock_location');
		$this->supplier = model('Supplier');
	}

	public function index(): void
	{
		$this->_reload();
	}

	public function item_search(): void
	{
		$suggestions = $this->item->get_search_suggestions($this->request->getGet('term'), ['search_custom' => FALSE, 'is_deleted' => FALSE], TRUE);
		$suggestions = array_merge($suggestions, $this->item_kit->get_search_suggestions($this->request->getGet('term')));

			$suggestions = $suggestions;	//TODO: needed?

		echo json_encode($suggestions);
	}

	public function stock_item_search(): void
	{
		$suggestions = $this->item->get_stock_search_suggestions($this->request->getGet('term'), ['search_custom' => FALSE, 'is_deleted' => FALSE], TRUE);
		$suggestions = array_merge($suggestions, $this->item_kit->get_search_suggestions($this->request->getGet('term')));

			$suggestions = $suggestions;	//TODO: needed?

		echo json_encode($suggestions);
	}

	public function select_supplier(): void
	{
		$supplier_id = $this->request->getPost('supplier');
		if($this->supplier->exists($supplier_id))
		{
			$this->receiving_lib->set_supplier($supplier_id);
		}

		$this->_reload();	//TODO: Hungarian notation
	}

	public function change_mode(): void
	{
		$stock_destination = $this->request->getPost('stock_destination');
		$stock_source = $this->request->getPost('stock_source');

		if((!$stock_source || $stock_source == $this->receiving_lib->get_stock_source()) &&
			(!$stock_destination || $stock_destination == $this->receiving_lib->get_stock_destination()))
		{
			$this->receiving_lib->clear_reference();
			$mode = $this->request->getPost('mode');
			$this->receiving_lib->set_mode($mode);
		}
		elseif($this->stock_location->is_allowed_location($stock_source, 'receivings'))
		{
			$this->receiving_lib->set_stock_source($stock_source);
			$this->receiving_lib->set_stock_destination($stock_destination);
		}

		$this->_reload();	//TODO: Hungarian notation
	}
	
	public function set_comment(): void
	{
		$this->receiving_lib->set_comment($this->request->getPost('comment'));
	}

	public function set_print_after_sale(): void
	{
		$this->receiving_lib->set_print_after_sale($this->request->getPost('recv_print_after_sale'));
	}
	
	public function set_reference(): void
	{
		$this->receiving_lib->set_reference($this->request->getPost('recv_reference'));
	}
	
	public function add(): void
	{
		$data = [];

		$mode = $this->receiving_lib->get_mode();
		$item_id_or_number_or_item_kit_or_receipt = $this->request->getPost('item');
		$this->token_lib->parse_barcode($quantity, $price, $item_id_or_number_or_item_kit_or_receipt);
		$quantity = ($mode == 'receive' || $mode == 'requisition') ? $quantity : -$quantity;
		$item_location = $this->receiving_lib->get_stock_source();
		$discount = config('OSPOS')->default_receivings_discount;
		$discount_type = config('OSPOS')->default_receivings_discount_type;

		if($mode == 'return' && $this->receiving->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->receiving_lib->return_entire_receiving($item_id_or_number_or_item_kit_or_receipt);
		}
		elseif($this->item_kit->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->receiving_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt, $item_location, $discount, $discount_type);
		}
		elseif(!$this->receiving_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location, $discount,  $discount_type))
		{
			$data['error'] = lang('Receivings.unable_to_add_item');
		}

		$this->_reload($data);	//TODO: Hungarian notation
	}

	public function edit_item($item_id): void
	{
		$data = [];

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|callback_numeric');	//TODO: need to upgrade form_validation
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|callback_numeric');
		$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|callback_numeric');

		$description = $this->request->getPost('description');	//TODO: Duplicated code
		$serialnumber = $this->request->getPost('serialnumber');
		$price = parse_decimals($this->request->getPost('price'));
		$quantity = parse_quantity($this->request->getPost('quantity'));
		$discount_type = $this->request->getPost('discount_type');
		$discount = $discount_type ? parse_quantity($this->request->getPost('discount')) : parse_decimals($this->request->getPost('discount'));

		$receiving_quantity = $this->request->getPost('receiving_quantity');

		if($this->form_validation->run() != FALSE)	//TODO Form validation
		{
			$this->receiving_lib->edit_item($item_id, $description, $serialnumber, $quantity, $discount, $discount_type, $price, $receiving_quantity);
		}
		else
		{
			$data['error']=lang('Receivings.error_editing_item');
		}

		$this->_reload($data);	//TODO: Hungarian notation
	}
	
	public function edit($receiving_id): void
	{
		$data = [];

		$data['suppliers'] = ['' => 'No Supplier'];
		foreach($this->supplier->get_all()->getResult() as $supplier)
		{
			$data['suppliers'][$supplier->person_id] = $supplier->first_name . ' ' . $supplier->last_name;
		}
	
		$data['employees'] = [];
		foreach($this->employee->get_all()->getResult() as $employee)
		{
			$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
		}
	
		$receiving_info = $this->receiving->get_info($receiving_id)->getRowArray();
		$data['selected_supplier_name'] = !empty($receiving_info['supplier_id']) ? $receiving_info['company_name'] : '';
		$data['selected_supplier_id'] = $receiving_info['supplier_id'];
		$data['receiving_info'] = $receiving_info;
	
		echo view('receivings/form', $data);
	}

	public function delete_item($item_number): void
	{
		$this->receiving_lib->delete_item($item_number);

		$this->_reload();	//TODO: Hungarian notation
	}
	
	public function delete($receiving_id = -1, $update_inventory = TRUE) : void
	{
		$employee_id = $this->employee->get_logged_in_employee_info()->person_id;
		$receiving_ids = $receiving_id == -1 ? $this->request->getPost('ids') : [$receiving_id];	//TODO: Replace -1 with constant
	
		if($this->receiving->delete_list($receiving_ids, $employee_id, $update_inventory))
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Receivings.successfully_deleted') . ' ' . count($receiving_ids) . ' ' . lang('Receivings.one_or_multiple'),
				'ids' => $receiving_ids]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Receivings.cannot_be_deleted')]);
		}
	}

	public function remove_supplier(): void
	{
		$this->receiving_lib->clear_reference();
		$this->receiving_lib->remove_supplier();

		$this->_reload();	//TODO: Hungarian notation
	}

	public function complete(): void
	{
		$data = [];
		
		$data['cart'] = $this->receiving_lib->get_cart();
		$data['total'] = $this->receiving_lib->get_total();
		$data['transaction_time'] = to_datetime(time());
		$data['mode'] = $this->receiving_lib->get_mode();
		$data['comment'] = $this->receiving_lib->get_comment();
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['payment_type'] = $this->request->getPost('payment_type');
		$data['show_stock_locations'] = $this->stock_location->show_locations('receivings');
		$data['stock_location'] = $this->receiving_lib->get_stock_source();
		if($this->request->getPost('amount_tendered') != NULL)
		{
			$data['amount_tendered'] = $this->request->getPost('amount_tendered');
			$data['amount_change'] = to_currency($data['amount_tendered'] - $data['total']);
		}
		
		$employee_id = $this->employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_info = '';
		$supplier_id = $this->receiving_lib->get_supplier();
		if($supplier_id != -1)
		{
			$supplier_info = $this->supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;	//TODO: duplicated code
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if(!empty($supplier_info->zip) or !empty($supplier_info->city))
			{
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
			}
			else
			{
				$data['supplier_location'] = '';
			}
		}

		//SAVE receiving to database
		$data['receiving_id'] = 'RECV ' . $this->receiving->save_value($data['cart'], $supplier_id, $employee_id, $data['comment'], $data['reference'], $data['payment_type'], $data['stock_location']);	//TODO: Reflection Exception

		$data = $data;

		if($data['receiving_id'] == 'RECV -1')
		{
			$data['error_message'] = lang('Receivings.transaction_failed');
		}
		else
		{
			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
		}

		$data['print_after_sale'] = $this->receiving_lib->is_print_after_sale();

		echo view("receivings/receipt",$data);

		$this->receiving_lib->clear_all();
	}

	public function requisition_complete(): void
	{
		if($this->receiving_lib->get_stock_source() != $this->receiving_lib->get_stock_destination()) 
		{
			foreach($this->receiving_lib->get_cart() as $item)
			{
				$this->receiving_lib->delete_item($item['line']);
				$this->receiving_lib->add_item($item['item_id'], $item['quantity'], $this->receiving_lib->get_stock_destination(), $item['discount_type']);
				$this->receiving_lib->add_item($item['item_id'], -$item['quantity'], $this->receiving_lib->get_stock_source(), $item['discount_type']);
			}
			
			$this->complete();
		}
		else 
		{
			$data['error'] = lang('Receivings.error_requisition');

			$this->_reload($data);	//TODO: Hungarian notation
		}
	}
	
	public function receipt($receiving_id): void
	{
		$receiving_info = $this->receiving->get_info($receiving_id)->getRowArray();
		$this->receiving_lib->copy_entire_receiving($receiving_id);
		$data['cart'] = $this->receiving_lib->get_cart();
		$data['total'] = $this->receiving_lib->get_total();
		$data['mode'] = $this->receiving_lib->get_mode();
		$data['transaction_time'] = to_datetime(strtotime($receiving_info['receiving_time']));
		$data['show_stock_locations'] = $this->stock_location->show_locations('receivings');
		$data['payment_type'] = $receiving_info['payment_type'];
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['receiving_id'] = 'RECV ' . $receiving_id;
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
		$employee_info = $this->employee->get_info($receiving_info['employee_id']);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_id = $this->receiving_lib->get_supplier();	//TODO: Duplicated code
		if($supplier_id != -1)
		{
			$supplier_info = $this->supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if(!empty($supplier_info->zip) or !empty($supplier_info->city))
			{
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
			}
			else
			{
				$data['supplier_location'] = '';
			}
		}

		$data['print_after_sale'] = FALSE;

		echo view("receivings/receipt", $data);

		$this->receiving_lib->clear_all();
	}

	private function _reload($data = []): void	//TODO: Hungarian notation
	{
		$data['cart'] = $this->receiving_lib->get_cart();
		$data['modes'] = ['receive' => lang('Receivings.receiving'), 'return' => lang('Receivings.return')];
		$data['mode'] = $this->receiving_lib->get_mode();
		$data['stock_locations'] = $this->stock_location->get_allowed_locations('receivings');
		$data['show_stock_locations'] = count($data['stock_locations']) > 1;
		if($data['show_stock_locations']) 
		{
			$data['modes']['requisition'] = lang('Receivings.requisition');
			$data['stock_source'] = $this->receiving_lib->get_stock_source();
			$data['stock_destination'] = $this->receiving_lib->get_stock_destination();
		}

		$data['total'] = $this->receiving_lib->get_total();
		$data['items_module_allowed'] = $this->employee->has_grant('items', $this->employee->get_logged_in_employee_info()->person_id);
		$data['comment'] = $this->receiving_lib->get_comment();
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['payment_options'] = $this->receiving->get_payment_options();

		$supplier_id = $this->receiving_lib->get_supplier();
		$supplier_info = '';
		if($supplier_id != -1)	//TODO: Duplicated Code... replace -1 with a constant
		{
			$supplier_info = $this->supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;
			if(!empty($supplier_info->zip) or !empty($supplier_info->city))
			{
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
			}
			else
			{
				$data['supplier_location'] = '';
			}
		}
		
		$data['print_after_sale'] = $this->receiving_lib->is_print_after_sale();

		echo view("receivings/receiving", $data);
	}

	/**
	 * @throws \ReflectionException
	 */
	public function save(int $receiving_id = -1): void	//TODO: Replace -1 with a constant
	{
		$newdate = $this->request->getPost('date');	//TODO: newdate does not follow naming conventions
		
		$date_formatter = date_create_from_format(config('OSPOS')->dateformat . ' ' . config('OSPOS')->timeformat, $newdate);
		$receiving_time = $date_formatter->format('Y-m-d H:i:s');

		$receiving_data = [
			'receiving_time' => $receiving_time,
			'supplier_id' => $this->request->getPost('supplier_id') ? $this->request->getPost('supplier_id') : NULL,
			'employee_id' => $this->request->getPost('employee_id'),
			'comment' => $this->request->getPost('comment'),
			'reference' => $this->request->getPost('reference') != '' ? $this->request->getPost('reference') : NULL
		];

		$this->inventory->update('RECV '.$receiving_id, ['trans_date' => $receiving_time]);
		if($this->receiving->update($receiving_id, $receiving_data))
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Receivings.successfully_updated'),
				'id' => $receiving_id
			]);
		}
		else
		{
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Receivings.unsuccessfully_updated'),
				'id' => $receiving_id
			]);
		}
	}

	public function cancel_receiving(): void
	{
		$this->receiving_lib->clear_all();

		$this->_reload();	//TODO: Hungarian Notation
	}
}
?>
