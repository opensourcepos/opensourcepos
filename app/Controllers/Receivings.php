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
use Config\Services;
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
	 * Returns search suggestions for an item. Used in app/Views/sales/register.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function getItemSearch(): void
	{
		$search = $this->request->getGet('term');
		$suggestions = $this->item->get_search_suggestions($search, ['search_custom' => false, 'is_deleted' => false], true);
		$suggestions = array_merge($suggestions, $this->item_kit->get_search_suggestions($search));

		echo json_encode($suggestions);
	}

	/**
	 * Gets search suggestions for a stock item. Used in app/Views/receivings/receiving.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function getStockItemSearch(): void
	{
		$search = $this->request->getGet('term');
		$suggestions = $this->item->get_stock_search_suggestions($search, ['search_custom' => false, 'is_deleted' => false], true);
		$suggestions = array_merge($suggestions, $this->item_kit->get_search_suggestions($search));

		echo json_encode($suggestions);
	}

	/**
	 * Set supplier if it exists in the database. Used in app/Views/receivings/receiving.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postSelectSupplier(): void
	{
		$supplier_id = $this->request->getPost('supplier', FILTER_SANITIZE_NUMBER_INT);
		if($this->supplier->exists($supplier_id))
		{
			$this->receiving_lib->set_supplier($supplier_id);
		}

		$this->_reload();	//TODO: Hungarian notation
	}

	/**
	 * Change receiving mode for current receiving. Used in app/Views/receivings/receiving.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postChangeMode(): void
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
	 * Sets receiving comment. Used in app/Views/receivings/receiving.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postSetComment(): void
	{
		$this->receiving_lib->set_comment($this->request->getPost('comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
	}

	/**
	 * Sets the print after sale flag for the receiving. Used in app/Views/receivings/receiving.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postSetPrintAfterSale(): void
	{
		$this->receiving_lib->set_print_after_sale($this->request->getPost('recv_print_after_sale') != null);
	}

	/**
	 * Sets the reference number for the receiving.  Used in app/Views/receivings/receiving.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postSetReference(): void
	{
		$this->receiving_lib->set_reference($this->request->getPost('recv_reference', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
	}

	/**
	 * Add an item to the receiving. Used in app/Views/receivings/receiving.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postAdd(): void
	{
		$data = [];

		$mode = $this->receiving_lib->get_mode();
		$item_id_or_number_or_item_kit_or_receipt = (int)$this->request->getPost('item', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
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
	 * Edit line item in current receiving. Used in app/Views/receivings/receiving.php
	 *
	 * @param $item_id
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postEditItem($item_id): void
	{
		$data = [];

		$validation_rule = [
			'price' => 'trim|required|decimal_locale',
			'quantity' => 'trim|required|decimal_locale',
			'discount' => 'trim|permit_empty|decimal_locale',
		];

		$price = parse_decimals($this->request->getPost('price'));
		$quantity = parse_quantity($this->request->getPost('quantity'));
		$discount = parse_decimals($this->request->getPost('discount'));
		$raw_receiving_quantity = parse_quantity($this->request->getPost('receiving_quantity'));

		$description = $this->request->getPost('description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);	//TODO: Duplicated code
		$serialnumber = $this->request->getPost('serialnumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
		$price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		$quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		$discount_type = $this->request->getPost('discount_type', FILTER_SANITIZE_NUMBER_INT);
		$discount = $discount_type
			? parse_quantity(filter_var($this->request->getPost('discount'), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION))
			: parse_decimals(filter_var($this->request->getPost('discount'), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

		$receiving_quantity = filter_var($raw_receiving_quantity, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

		if($this->validate($validation_rule))
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
	 * Edit a receiving. Used in app/Controllers/Receivings.php
	 *
	 * @param $receiving_id
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function getEdit($receiving_id): void
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
		$balance_due = round($receiving_info['amount_due'] - $receiving_info['amount_tendered'] + $receiving_info['cash_refund'], totals_decimals(), PHP_ROUND_HALF_UP);

		echo view('receivings/form', $data);
	}

	/**
	 * Deletes an item from the current receiving. Used in app/Views/receivings/receiving.php
	 *
	 * @param $item_number
	 * @return void
	 * @noinspection PhpUnused
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
	 * Removes a supplier from a receiving. Used in app/Views/receivings/receiving.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function getRemoveSupplier(): void
	{
		$this->receiving_lib->clear_reference();
		$this->receiving_lib->remove_supplier();

		$this->_reload();	//TODO: Hungarian notation
	}

	/**
	 * Complete and finalize receiving.  Used in app/Views/receivings/receiving.php
	 *
	 * @throws ReflectionException
	 * @noinspection PhpUnused
	 */
	public function postComplete(): void
	{
		$amount_tendered = parse_decimals($this->request->getPost('amount_tendered'));
		$data = [];

		$data['cart'] = $this->receiving_lib->get_cart();
		$data['total'] = $this->receiving_lib->get_total();
		$data['transaction_time'] = date('Y-m-d H:i:s'); // Fecha y hora actuales
		$data['mode'] = $this->receiving_lib->get_mode();
		$data['comment'] = $this->receiving_lib->get_comment();
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['term_days'] = $this->receiving_lib->get_term_days();
		$data['payment_type'] = $this->request->getPost('payment_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$data['payments'] = $this->receiving_lib->get_payments(); // Obtener pagos del carrito
		$data['show_stock_locations'] = $this->stock_location->show_locations('receivings');
		$data['stock_location'] = $this->receiving_lib->get_stock_source();

		if ($this->request->getPost('amount_tendered') != null) {
			$data['amount_tendered'] = filter_var($amount_tendered, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$data['amount_change'] = $data['amount_tendered'] - $data['total'];
		} else {
			$data['amount_change'] = 0;
		}

		$employee_id = $this->employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

		$supplier_id = $this->receiving_lib->get_supplier();
		if ($supplier_id != -1) {
			$supplier_info = $this->supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;
			$data['first_name'] = $supplier_info->first_name;
			$data['last_name'] = $supplier_info->last_name;
			$data['supplier_email'] = $supplier_info->email;
			$data['supplier_address'] = $supplier_info->address_1;

			if (!empty($supplier_info->zip) || !empty($supplier_info->city)) {
				$data['supplier_location'] = $supplier_info->zip . ' ' . $supplier_info->city;
			} else {
				$data['supplier_location'] = '';
			}
		}

		// Guardar el registro de compra en la base de datos
		$receiving_id = $this->receiving->save_value(
			$data['cart'],
			$supplier_id,
			$employee_id,
			$data['comment'],
			$data['reference'],
			$data['term_days'],
			$data['payment_type']
		);

		if ($receiving_id == -1) {
			$data['error_message'] = lang('Receivings.transaction_failed');
		} else {
			$data['receiving_id'] = $receiving_id;

			// Calcular el cambio (cash_refund)
			$total_payments = array_sum(array_column($data['payments'], 'payment_amount'));
			$cash_refund = max(0, $total_payments - $data['total']);

			// Guardar los pagos en la base de datos
			foreach ($data['payments'] as $payment) {
				$this->receiving->save_payment([
					'receiving_id' => $receiving_id, // Utiliza el ID generado
					'payment_type' => $payment['payment_type'],
					'payment_amount' => $payment['payment_amount'],
					'cash_refund' => ($payment['payment_type'] === lang('Sales.cash')) ? $cash_refund : 0,
					'employee_id' => $employee_id,
					'payment_time' => $data['transaction_time'], // Fecha y hora actuales
				]);
			}

			$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($receiving_id);
		}

		$data['print_after_sale'] = $this->receiving_lib->is_print_after_sale();

		echo view("receivings/receipt", $data);

		// Limpiar pagos y otros datos
		$this->receiving_lib->clear_payments(); // Limpia los pagos
		$this->receiving_lib->clear_all();
	}

	/**
	 * Complete a receiving requisition. Used in app/Views/receivings/receiving.php.
	 *
	 * @throws ReflectionException
	 * @noinspection PhpUnused
	 */
	public function postRequisitionComplete(): void
	{
	    $data = [];

	    // Check if stock locations are different
	    if ($this->receiving_lib->get_stock_source() != $this->receiving_lib->get_stock_destination()) {
	        // Process items in the cart
	        foreach ($this->receiving_lib->get_cart() as $item) {
	            $this->receiving_lib->delete_item($item['line']);
	            $this->receiving_lib->add_item($item['item_id'], $item['quantity'], $this->receiving_lib->get_stock_destination(), $item['discount_type']);
	            $this->receiving_lib->add_item($item['item_id'], -$item['quantity'], $this->receiving_lib->get_stock_source(), $item['discount_type']);
	        }

	        // Prepare data to complete the requisition
	        $data['cart'] = $this->receiving_lib->get_cart();
	        $data['total'] = $this->receiving_lib->get_total();
	        $data['transaction_time'] = to_datetime(time());
	        $data['mode'] = $this->receiving_lib->get_mode();
	        $data['comment'] = $this->receiving_lib->get_comment();
	        $data['term_days'] = $this->receiving_lib->get_term_days();
	        $data['reference'] = $this->receiving_lib->get_reference();
	        $data['show_stock_locations'] = $this->stock_location->show_locations('receivings');
	        $data['stock_location_source'] = $this->receiving_lib->get_stock_source();
	        $data['stock_location_destination'] = $this->receiving_lib->get_stock_destination();

	        $employee_id = $this->employee->get_logged_in_employee_info()->person_id;
	        $employee_info = $this->employee->get_info($employee_id);
	        $data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

	        // Save the requisition in the database
	        $data['receiving_id'] = 'REQ ' . $this->receiving->save_requisition(
	            $data['cart'],
	            $employee_id,
	            $data['comment'],
	            $data['reference'],
	            $data['stock_location_source'],
	            $data['stock_location_destination']
	        );

	        if ($data['receiving_id'] == 'REQ -1') {
	            $data['error_message'] = lang('Receivings.transaction_failed');
	        } else {
	            $data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['receiving_id']);
	        }

	        $data['print_after_sale'] = $this->receiving_lib->is_print_after_sale();

	        echo view("receivings/receipt", $data);

	        $this->receiving_lib->clear_all();
	    } else {
	        $data['error'] = lang('Receivings.error_requisition');
	        $this->_reload($data);
	    }
	}

	/**
	 * Gets the receipt for a receiving. Used in app/Views/receivings/form.php
	 *
	 * @param $receiving_id
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function getReceipt($receiving_id): void
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
	 * @param array $data
	 * @return void
	 */
	private function _reload(array $data = []): void
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
		$data['term_days'] = $this->receiving_lib->get_term_days();
		$data['reference'] = $this->receiving_lib->get_reference();
		$data['payment_options'] = $this->receiving->get_payment_options();
		$data['selected_payment_type'] = $this->receiving_lib->get_payment_type();
		$data['amount_due'] = $this->receiving_lib->get_total() - $this->receiving_lib->get_payments_total();

		// Agregar pagos al arreglo de datos
		$data['payments'] = $this->receiving_lib->get_payments();

		// Inicializar tabindex
		$data['tabindex'] = 0;

		$supplier_id = $this->receiving_lib->get_supplier();

		if($supplier_id != -1)
		{
			$supplier_info = $this->supplier->get_info($supplier_id);
			$data['supplier'] = $supplier_info->company_name;
			$data['avatar'] = $supplier_info->avatar;
			$data['gender'] = $supplier_info->gender;
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
	public function save(int $receiving_id = -1): void
	{
		$newdate = $this->request->getPost('date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

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
	 * Cancel an in-process receiving. Used in app/Views/receivings/receiving.php
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function postCancelReceiving(): void
	{
		// Limpiar los pagos
		$this->receiving_lib->clear_payments();

		// Limpiar todos los datos relacionados con la transacción
		$this->receiving_lib->clear_all();

		// Recargar la vista
		$this->_reload();
	}

	public function postAddPayment(): void
	{
		$data = [];
		$payment_type = $this->request->getPost('payment_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	
		// Validar el tipo de pago
		if ($payment_type !== lang('Sales.giftcard')) {
			$rules = ['amount_tendered' => 'trim|required|decimal_locale'];
			$messages = ['amount_tendered' => lang('Sales.must_enter_numeric')];
		} else {
			$rules = ['amount_tendered' => 'trim|required'];
			$messages = ['amount_tendered' => lang('Sales.must_enter_numeric_giftcard')];
		}
	
		if (!$this->validate($rules, $messages)) {
			$data['error'] = $payment_type === lang('Sales.giftcard')
				? lang('Sales.must_enter_numeric_giftcard')
				: lang('Sales.must_enter_numeric');
		} else {
			if ($payment_type === lang('Sales.giftcard')) {
				$amount_tendered = parse_decimals($this->request->getPost('amount_tendered'));
				$giftcard_num = filter_var($amount_tendered, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_ALLOW_FRACTION);
	
				$payments = $this->receiving_lib->get_payments();
				$payment_type = $payment_type . ':' . $giftcard_num;
				$current_payments_with_giftcard = isset($payments[$payment_type]) ? $payments[$payment_type]['payment_amount'] : 0;
	
				$giftcard = model(Giftcard::class);
				$cur_giftcard_value = $giftcard->get_giftcard_value($giftcard_num);
	
				if (($cur_giftcard_value - $current_payments_with_giftcard) <= 0) {
					$data['error'] = lang('Giftcards.remaining_balance', [$giftcard_num, $cur_giftcard_value]);
				} else {
					$amount_tendered = min($this->receiving_lib->get_amount_due(), $cur_giftcard_value);
					$this->receiving_lib->add_payment($payment_type, $amount_tendered);
				}
			} elseif ($payment_type === lang('Sales.cash')) {
				$raw_amount_tendered = parse_decimals($this->request->getPost('amount_tendered'));
				$amount_tendered = filter_var($raw_amount_tendered, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
				$this->receiving_lib->add_payment($payment_type, $amount_tendered);
			} else {
				$raw_amount_tendered = parse_decimals($this->request->getPost('amount_tendered'));
				$amount_tendered = filter_var($raw_amount_tendered, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
				$this->receiving_lib->add_payment($payment_type, $amount_tendered);
			}
		}
	
		$this->_reload($data);
	}

	/**
	 * Multiple Payments. Used in app/Views/sales/register.php
	 *
	 * @param string $payment_id
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function getDeletePayment(string $payment_id): void
	{
		$this->receiving_lib->delete_payment($payment_id);

		$this->_reload();
	}
}
