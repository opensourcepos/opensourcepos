<?php

namespace App\Controllers;

use App\Libraries\Receiving_lib;
use App\Libraries\Token_lib;
use App\Libraries\Barcode_lib;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Item_kit;
use App\Models\Receiving;
use App\Models\Stock_location;
use App\Models\Supplier;
use Config\OSPOS;
use ReflectionException;

class Receivings extends Secure_Controller
{
	 private Receiving_lib $receiving_lib;
	 private Token_lib $token_lib;
	 private Barcode_lib $barcode_lib;
	 private Inventory $inventory;
	 private Item $item;
	 private Item_kit $item_kit;
	 private Receiving $receiving;
	 private Stock_location $stock_location;
	 private Supplier $supplier;
	 private array $config;

	public function __construct()
	{
		parent::__construct('receivings');

		$this->receiving_lib = new Receiving_lib();
		$this->token_lib = new Token_lib();
		$this->barcode_lib = new Barcode_lib();

		$this->inventory = model(Inventory::class);
		$this->item_kit = model(Item_kit::class);
		$this->item = model(Item::class);
		$this->receiving = model(Receiving::class);
		$this->stock_location = model(Stock_location::class);
		$this->supplier = model(Supplier::class);
		$this->config = config(OSPOS::class)->settings;
	}

	/**
	 * @return void
	 */
	public function getIndex(): void
	{
		$this->_reload();
	}

	/**
	 * Called in the view.
	 * @return void
	 */
	public function getItemSearch(): void
	{
		$suggestions = $this->item->get_search_suggestions($this->request->getGet('term', FILTER_SANITIZE_FULL_SPECIAL_CHARS), ['search_custom' => false, 'is_deleted' => false], true);
		$suggestions = array_merge($suggestions, $this->item_kit->get_search_suggestions($this->request->getGet('term', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

		echo json_encode($suggestions);
	}

	/**
	 * Called in the view.
	 * @return void
	 */
	public function getStockItemSearch(): void
	{
		$suggestions = $this->item->get_stock_search_suggestions($this->request->getGet('term', FILTER_SANITIZE_FULL_SPECIAL_CHARS), ['search_custom' => false, 'is_deleted' => false], true);
		$suggestions = array_merge($suggestions, $this->item_kit->get_search_suggestions($this->request->getGet('term', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

		echo json_encode($suggestions);
	}

	/**
	 * Called in the view.
	 * @return void
	 */
	public function select_supplier(): void
	{
		$supplier_id = $this->request->getPost('supplier', FILTER_SANITIZE_NUMBER_INT);
		if($this->supplier->exists($supplier_id))
		{
			$this->receiving_lib->set_supplier($supplier_id);
		}

		$this->_reload();	//TODO: Hungarian notation
	}

	/**
	 * @return void
	 */
	public function change_mode(): void
	{
		$stock_destination = $this->request->getPost('stock_destination', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$stock_source = $this->request->getPost('stock_source', FILTER_SANITIZE_NUMBER_INT);

		if((!$stock_source || $stock_source == $this->receiving_lib->get_stock_source()) &&
			(!$stock_destination || $stock_destination == $this->receiving_lib->get_stock_destination()))
		{
			$this->receiving_lib->clear_reference();
			$mode = $this->request->getPost('mode', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$this->receiving_lib->set_mode($mode);
		}
		elseif($this->stock_location->is_allowed_location($stock_source, 'receivings'))
		{
			$this->receiving_lib->set_stock_source($stock_source);
			$this->receiving_lib->set_stock_destination($stock_destination);
		}

		$this->_reload();	//TODO: Hungarian notation
	}

	/**
	 * @return void
	 */
	public function set_comment(): void
	{
		$this->receiving_lib->set_comment($this->request->getPost('comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
	}

	/**
	 * Called in the view.
	 * @return void
	 */
	public function set_print_after_sale(): void
	{
		$this->receiving_lib->set_print_after_sale($this->request->getPost('recv_print_after_sale', FILTER_SANITIZE_NUMBER_INT));
	}

	/**
	 * @return void
	 */
	public function set_reference(): void
	{
		$this->receiving_lib->set_reference($this->request->getPost('recv_reference', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
	}

	/**
	 * @return void
	 */
	public function add(): void
	{
		$data = [];

		$mode = $this->receiving_lib->get_mode();
		$item_id_or_number_or_item_kit_or_receipt = $this->request->getPost('item', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$this->token_lib->parse_barcode($quantity, $price, $item_id_or_number_or_item_kit_or_receipt);
		$quantity = ($mode == 'receive' || $mode == 'requisition') ? $quantity : -$quantity;
		$item_location = $this->receiving_lib->get_stock_source();
		$discount = $this->config['default_receivings_discount'];
		$discount_type = $this->config['default_receivings_discount_type'];

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

	/**
	 * Called in the view.
	 *
	 * @param $item_id
	 * @return void
	 */
	public function postEditItem($item_id): void
	{
		$data = [];

		$this->validator->setRule('price', 'lang:items_price', 'required|numeric');
		$this->validator->setRule('quantity', 'lang:items_quantity', 'required|numeric');
		$this->validator->setRule('discount', 'lang:items_discount', 'required|numeric');

		$description = $this->request->getPost('description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);	//TODO: Duplicated code
		$serialnumber = $this->request->getPost('serialnumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$price = parse_decimals($this->request->getPost('price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
		$quantity = parse_quantity($this->request->getPost('quantity', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
		$discount_type = $this->request->getPost('discount_type', FILTER_SANITIZE_NUMBER_INT);
		$discount = $discount_type
			? parse_quantity($this->request->getPost('discount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION))
			: parse_decimals($this->request->getPost('discount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

		$receiving_quantity = $this->request->getPost('receiving_quantity', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

		if(!$this->validate([]))
		{
			$this->receiving_lib->edit_item($item_id, $description, $serialnumber, $quantity, $discount, $discount_type, $price, $receiving_quantity);
		}
		else
		{
			$data['error']=lang('Receivings.error_editing_item');
		}

		$this->_reload($data);	//TODO: Hungarian notation
	}

	/**
	 * @param $receiving_id
	 * @return void
	 */
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

	/**
	 * Called in the view.
	 *
	 * @param $item_number
	 * @return void
	 */
	public function getDeleteItem($item_number): void
	{
		$this->receiving_lib->delete_item($item_number);

		$this->_reload();	//TODO: Hungarian notation
	}

	/**
	 * @throws ReflectionException
	 */
	public function postDelete(int $receiving_id = -1, bool $update_inventory = true) : void
	{
		$employee_id = $this->employee->get_logged_in_employee_info()->person_id;
		$receiving_ids = $receiving_id == -1 ? $this->request->getPost('ids', FILTER_SANITIZE_NUMBER_INT) : [$receiving_id];	//TODO: Replace -1 with constant

		if($this->receiving->delete_list($receiving_ids, $employee_id, $update_inventory))	//TODO: Likely need to surround this block of code in a try-catch to catch the ReflectionException
		{
			echo json_encode ([
				'success' => true,
				'message' => lang('Receivings.successfully_deleted') . ' ' . count($receiving_ids) . ' ' . lang('Receivings.one_or_multiple'),
				'ids' => $receiving_ids]);
		}
		else
		{
			echo json_encode (['success' => false, 'message' => lang('Receivings.cannot_be_deleted')]);
		}
	}

	/**
	 * Called in the view.
	 * @return void
	 */
	public function remove_supplier(): void
	{
		$this->receiving_lib->clear_reference();
		$this->receiving_lib->remove_supplier();

		$this->_reload();	//TODO: Hungarian notation
	}

	/**
	 * @throws ReflectionException
	 */
	public function complete(): void
	{
		$data = [];

		$data['cart'] = $this->receiving_lib->get_cart();
		$data['total'] = $this->receiving_lib->get_total();
		$data['transaction_time'] = to_datetime(time());
		$data['mode'] = $this->receiving_lib->get_mode();
		$data['comment'] = $this->receiving_lib->get_comment();
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['payment_type'] = $this->request->getPost('payment_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$data['show_stock_locations'] = $this->stock_location->show_locations('receivings');
		$data['stock_location'] = $this->receiving_lib->get_stock_source();
		if($this->request->getPost('amount_tendered') != null)
		{
			$data['amount_tendered'] = $this->request->getPost('amount_tendered', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$data['amount_change'] = to_currency($data['amount_tendered'] - $data['total']);
		}

		$employee_id = $this->employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

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
		$data['receiving_id'] = 'RECV ' . $this->receiving->save_value($data['cart'], $supplier_id, $employee_id, $data['comment'], $data['reference'], $data['payment_type'], $data['stock_location']);

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

	/**
	 * Called in the view.
	 *
	 * @throws ReflectionException
	 */
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

	/**
	 * @param $receiving_id
	 * @return void
	 */
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

		$data['print_after_sale'] = false;

		echo view("receivings/receipt", $data);

		$this->receiving_lib->clear_all();
	}

	/**
	 * @param $data
	 * @return void
	 */
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
	 * @throws ReflectionException
	 */
	public function save(int $receiving_id = -1): void	//TODO: Replace -1 with a constant
	{
		$newdate = $this->request->getPost('date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);	//TODO: newdate does not follow naming conventions

		$date_formatter = date_create_from_format($this->config['dateformat'] . ' ' . $this->config['timeformat'], $newdate);
		$receiving_time = $date_formatter->format('Y-m-d H:i:s');

		$receiving_data = [
			'receiving_time' => $receiving_time,
			'supplier_id' => $this->request->getPost('supplier_id') ? $this->request->getPost('supplier_id', FILTER_SANITIZE_NUMBER_INT) : null,
			'employee_id' => $this->request->getPost('employee_id', FILTER_SANITIZE_NUMBER_INT),
			'comment' => $this->request->getPost('comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'reference' => $this->request->getPost('reference') != '' ? $this->request->getPost('reference', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null
		];

		$this->inventory->update('RECV '.$receiving_id, ['trans_date' => $receiving_time]);
		if($this->receiving->update($receiving_id, $receiving_data))
		{
			echo json_encode ([
				'success' => true,
				'message' => lang('Receivings.successfully_updated'),
				'id' => $receiving_id
			]);
		}
		else
		{
			echo json_encode ([
				'success' => false,
				'message' => lang('Receivings.unsuccessfully_updated'),
				'id' => $receiving_id
			]);
		}
	}

	/**
	 * Called in the view.
	 * @return void
	 */
	public function cancel_receiving(): void
	{
		$this->receiving_lib->clear_all();

		$this->_reload();	//TODO: Hungarian Notation
	}
}
