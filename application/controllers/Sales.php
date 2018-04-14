<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

define('PRICE_MODE_STANDARD', 0);
define('PRICE_MODE_KIT', 1);

class Sales extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('sales');

		$this->load->library('sale_lib');
		$this->load->library('barcode_lib');
		$this->load->library('email_lib');
		$this->load->library('token_lib');
	}

	public function index()
	{
		$this->_reload();
	}

	public function manage()
	{
		$person_id = $this->session->userdata('person_id');

		if(!$this->Employee->has_grant('reports_sales', $person_id))
		{
			redirect('no_access/sales/reports_sales');
		}
		else
		{
			$data['table_headers'] = get_sales_manage_table_headers();

			// filters that will be loaded in the multiselect dropdown
			if($this->config->item('invoice_enable') == TRUE)
			{
				$data['filters'] = array('only_cash' => $this->lang->line('sales_cash_filter'),
					'only_due' => $this->lang->line('sales_due_filter'),
					'only_check' => $this->lang->line('sales_check_filter'),
					'only_invoices' => $this->lang->line('sales_invoice_filter'));
			}
			else
			{
				$data['filters'] = array('only_cash' => $this->lang->line('sales_cash_filter'),
					'only_due' => $this->lang->line('sales_due_filter'),
					'only_check' => $this->lang->line('sales_check_filter'));
			}

			$this->load->view('sales/manage', $data);
		}
	}

	public function get_row($row_id)
	{
		$sale_info = $this->Sale->get_info($row_id)->row();
		$data_row = $this->xss_clean(get_sale_data_row($sale_info));

		echo json_encode($data_row);
	}

	public function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort = $this->input->get('sort');
		$order = $this->input->get('order');

		$filters = array('sale_type' => 'all',
						 'location_id' => 'all',
						 'start_date' => $this->input->get('start_date'),
						 'end_date' => $this->input->get('end_date'),
						 'only_cash' => FALSE,
						 'only_due' => FALSE,
						 'only_check' => FALSE,
						 'only_invoices' => $this->config->item('invoice_enable') && $this->input->get('only_invoices'),
						 'is_valid_receipt' => $this->Sale->is_valid_receipt($search));

		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), TRUE);
		$filters = array_merge($filters, $filledup);

		$sales = $this->Sale->search($search, $filters, $limit, $offset, $sort, $order);
		$total_rows = $this->Sale->get_found_rows($search, $filters);
		$payments = $this->Sale->get_payments_summary($search, $filters);
		$payment_summary = $this->xss_clean(get_sales_manage_payments_summary($payments, $sales));

		$data_rows = array();
		foreach($sales->result() as $sale)
		{
			$data_rows[] = $this->xss_clean(get_sale_data_row($sale));
		}

		if($total_rows > 0)
		{
			$data_rows[] = $this->xss_clean(get_sale_data_last_row($sales));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows, 'payment_summary' => $payment_summary));
	}

	public function item_search()
	{
		$suggestions = array();
		$receipt = $search = $this->input->get('term') != '' ? $this->input->get('term') : NULL;

		if($this->sale_lib->get_mode() == 'return' && $this->Sale->is_valid_receipt($receipt))
		{
			// if a valid receipt or invoice was found the search term will be replaced with a receipt number (POS #)
			$suggestions[] = $receipt;
		}
		$suggestions = array_merge($suggestions, $this->Item->get_search_suggestions($search, array('search_custom' => FALSE, 'is_deleted' => FALSE), TRUE));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_search_suggestions($search));

		$suggestions = $this->xss_clean($suggestions);

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$search = $this->input->post('term') != '' ? $this->input->post('term') : NULL;

		$suggestions = $this->xss_clean($this->Sale->get_search_suggestions($search));

		echo json_encode($suggestions);
	}

	public function select_customer()
	{
		$customer_id = $this->input->post('customer');
		if($this->Customer->exists($customer_id))
		{
			$this->sale_lib->set_customer($customer_id);
			$discount_percent = $this->Customer->get_info($customer_id)->discount_percent;

			// apply customer default discount to items that have 0 discount
			if($discount_percent != '')
			{
				$this->sale_lib->apply_customer_discount($discount_percent);
			}
		}

		$this->_reload();
	}

	public function change_mode()
	{
		$mode = $this->input->post('mode');
		$this->sale_lib->set_mode($mode);

		if($mode == 'sale')
		{
			$this->sale_lib->set_sale_type(SALE_TYPE_POS);
		}
		else if($mode == 'sale_quote')
		{
			$this->sale_lib->set_sale_type(SALE_TYPE_QUOTE);
		}
		else if($mode == 'sale_work_order')
		{
			$this->sale_lib->set_sale_type(SALE_TYPE_WORK_ORDER);
		}
		else if($mode == 'sale_invoice')
		{
			$this->sale_lib->set_sale_type(SALE_TYPE_INVOICE);
		}
		else
		{
			$this->sale_lib->set_sale_type(SALE_TYPE_RETURN);
		}

		$stock_location = $this->input->post('stock_location');
		if(!$stock_location || $stock_location == $this->sale_lib->get_sale_location())
		{
			$dinner_table = $this->input->post('dinner_table');
			$this->sale_lib->set_dinner_table($dinner_table);

		}
		elseif($this->Stock_location->is_allowed_location($stock_location, 'sales'))
		{
			$this->sale_lib->set_sale_location($stock_location);
		}

		$this->_reload();
	}

	public function change_register_mode($sale_type)
	{
		if($sale_type == SALE_TYPE_POS)
		{
			$this->sale_lib->set_mode('sale');
		}
		elseif($sale_type == SALE_TYPE_QUOTE)
		{
			$this->sale_lib->set_mode('sale_quote');
		}
		elseif($sale_type == SALE_TYPE_WORK_ORDER)
		{
			$this->sale_lib->set_mode('sale_work_order');
		}
		elseif($sale_type == SALE_TYPE_INVOICE)
		{
			$this->sale_lib->set_mode('sale_invoice');
		}
		elseif($sale_type == SALE_TYPE_RETURN)
		{
			$this->sale_lib->set_mode('return');
		}
		else
		{
			$this->sale_lib->set_mode('sale');
		}
	}

	public function set_comment()
	{
		$this->sale_lib->set_comment($this->input->post('comment'));
	}

	public function set_invoice_number()
	{
		$this->sale_lib->set_invoice_number($this->input->post('sales_invoice_number'));
	}

	public function set_invoice_number_enabled()
	{
		$this->sale_lib->set_invoice_number_enabled($this->input->post('sales_invoice_number_enabled'));
	}

	public function set_payment_type()
	{
		$this->sale_lib->set_payment_type($this->input->post('selected_payment_type'));
		$this->_reload();
	}

	public function set_print_after_sale()
	{
		$this->sale_lib->set_print_after_sale($this->input->post('sales_print_after_sale'));
	}

	public function set_price_work_orders()
	{
		$this->sale_lib->set_price_work_orders($this->input->post('price_work_orders'));
	}

	public function set_email_receipt()
	{
		$this->sale_lib->set_email_receipt($this->input->post('email_receipt'));
	}

	// Multiple Payments
	public function add_payment()
	{
		$data = array();

		$payment_type = $this->input->post('payment_type');
		if($payment_type != $this->lang->line('sales_giftcard'))
		{
			$this->form_validation->set_rules('amount_tendered', 'lang:sales_amount_tendered', 'trim|required|callback_numeric');
		}
		else
		{
			$this->form_validation->set_rules('amount_tendered', 'lang:sales_amount_tendered', 'trim|required');
		}

		if($this->form_validation->run() == FALSE)
		{
			if($payment_type == $this->lang->line('sales_giftcard'))
			{
				$data['error'] = $this->lang->line('sales_must_enter_numeric_giftcard');
			}
			else
			{
				$data['error'] = $this->lang->line('sales_must_enter_numeric');
			}
		}
		else
		{
			if($payment_type == $this->lang->line('sales_giftcard'))
			{
				// in case of giftcard payment the register input amount_tendered becomes the giftcard number
				$giftcard_num = $this->input->post('amount_tendered');

				$payments = $this->sale_lib->get_payments();
				$payment_type = $payment_type . ':' . $giftcard_num;
				$current_payments_with_giftcard = isset($payments[$payment_type]) ? $payments[$payment_type]['payment_amount'] : 0;
				$cur_giftcard_value = $this->Giftcard->get_giftcard_value($giftcard_num);
				$cur_giftcard_customer = $this->Giftcard->get_giftcard_customer($giftcard_num);
				$customer_id = $this->sale_lib->get_customer();
				if(isset($cur_giftcard_customer) && $cur_giftcard_customer != $customer_id)
				{
					$data['error'] = $this->lang->line('giftcards_cannot_use', $giftcard_num);
				}
				elseif(($cur_giftcard_value - $current_payments_with_giftcard) <= 0 && $this->sale_lib->get_mode() == 'sale')
				{
					$data['error'] = $this->lang->line('giftcards_remaining_balance', $giftcard_num, to_currency($cur_giftcard_value));
				}
				else
				{
					$new_giftcard_value = $this->Giftcard->get_giftcard_value($giftcard_num) - $this->sale_lib->get_amount_due();
					$new_giftcard_value = $new_giftcard_value >= 0 ? $new_giftcard_value : 0;
					$this->sale_lib->set_giftcard_remainder($new_giftcard_value);
					$new_giftcard_value = str_replace('$', '\$', to_currency($new_giftcard_value));
					$data['warning'] = $this->lang->line('giftcards_remaining_balance', $giftcard_num, $new_giftcard_value);
					$amount_tendered = min($this->sale_lib->get_amount_due(), $this->Giftcard->get_giftcard_value($giftcard_num));

					$this->sale_lib->add_payment($payment_type, $amount_tendered);
				}
			}
			elseif($payment_type == $this->lang->line('sales_rewards'))
			{
				$customer_id = $this->sale_lib->get_customer();
				$package_id = $this->Customer->get_info($customer_id)->package_id;
				if(!empty($package_id))
				{
					$package_name = $this->Customer_rewards->get_name($package_id);
					$points = $this->Customer->get_info($customer_id)->points;
					$points = ($points == NULL ? 0 : $points);

					$payments = $this->sale_lib->get_payments();
					$payment_type = $payment_type;
					$current_payments_with_rewards = isset($payments[$payment_type]) ? $payments[$payment_type]['payment_amount'] : 0;
					$cur_rewards_value = $points;

					if(($cur_rewards_value - $current_payments_with_rewards) <= 0)
					{
						$data['error'] = $this->lang->line('rewards_remaining_balance') . to_currency($cur_rewards_value);
					}
					else
					{
						$new_reward_value = $points - $this->sale_lib->get_amount_due();
						$new_reward_value = $new_reward_value >= 0 ? $new_reward_value : 0;
						$this->sale_lib->set_rewards_remainder($new_reward_value);
						$new_reward_value = str_replace('$', '\$', to_currency($new_reward_value));
						$data['warning'] = $this->lang->line('rewards_remaining_balance'). $new_reward_value;
						$amount_tendered = min($this->sale_lib->get_amount_due(), $points);

						$this->sale_lib->add_payment($payment_type, $amount_tendered);
					}
				}
			}
			else
			{
				$amount_tendered = $this->input->post('amount_tendered');
				$this->sale_lib->add_payment($payment_type, $amount_tendered);
			}
		}

		$this->_reload($data);
	}

	// Multiple Payments
	public function delete_payment($payment_id)
	{
		$this->sale_lib->delete_payment($payment_id);

		$this->_reload();
	}

	public function add()
	{
		$data = array();

		$discount = 0;

		// check if any discount is assigned to the selected customer
		$customer_id = $this->sale_lib->get_customer();
		if($customer_id != -1)
		{
			// load the customer discount if any
			$discount_percent = $this->Customer->get_info($customer_id)->discount_percent;
			if($discount_percent != '')
			{
				$discount = $discount_percent;
			}
		}

		// if the customer discount is 0 or no customer is selected apply the default sales discount
		if($discount == 0)
		{
			$discount = $this->config->item('default_sales_discount');
		}

		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		$this->barcode_lib->parse_barcode_fields($quantity, $item_id_or_number_or_item_kit_or_receipt);
		$mode = $this->sale_lib->get_mode();
		$quantity = ($mode == 'return') ? -$quantity : $quantity;
		$item_location = $this->sale_lib->get_sale_location();

		if($mode == 'return' && $this->Sale->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->sale_lib->return_entire_sale($item_id_or_number_or_item_kit_or_receipt);
		}
		elseif($this->Item_kit->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt))
		{
			// Add kit item to order if one is assigned
			$pieces = explode(' ', $item_id_or_number_or_item_kit_or_receipt);
			$item_kit_id = $pieces[1];
			$item_kit_info = $this->Item_kit->get_info($item_kit_id);
			$kit_item_id = $item_kit_info->kit_item_id;
			$kit_price_option = $item_kit_info->price_option;
			$kit_print_option = $item_kit_info->print_option; // 0-all, 1-priced, 2-kit-only

			if($item_kit_info->kit_discount_percent != 0 && $item_kit_info->kit_discount_percent > $discount)
			{
				$discount = $item_kit_info->kit_discount_percent;
			}

			$price = NULL;
			$print_option = PRINT_ALL; // Always include in list of items on invoice

			if(!empty($kit_item_id))
			{
				if(!$this->sale_lib->add_item($kit_item_id, $quantity, $item_location, $discount, PRICE_MODE_STANDARD))
				{
					$data['error'] = $this->lang->line('sales_unable_to_add_item');
				}
				else
				{
					$data['warning'] = $this->sale_lib->out_of_stock($item_kit_id, $item_location);
				}
			}

			// Add item kit items to order
			$stock_warning = NULL;
			if(!$this->sale_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt, $item_location, $discount, $kit_price_option, $kit_print_option, $stock_warning))
			{
				$data['error'] = $this->lang->line('sales_unable_to_add_item');
			}
			elseif($stock_warning != NULL)
			{
				$data['warning'] = $stock_warning;
			}
		}
		else
		{
			if(!$this->sale_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location, $discount, PRICE_MODE_STANDARD))
			{
				$data['error'] = $this->lang->line('sales_unable_to_add_item');
			}
			else
			{
				$data['warning'] = $this->sale_lib->out_of_stock($item_id_or_number_or_item_kit_or_receipt, $item_location);
			}
		}
		$this->_reload($data);
	}

	public function edit_item($item_id)
	{
		$data = array();

		$this->form_validation->set_rules('price', 'lang:sales_price', 'required|callback_numeric');
		$this->form_validation->set_rules('quantity', 'lang:sales_quantity', 'required|callback_numeric');
		$this->form_validation->set_rules('discount', 'lang:sales_discount', 'required|callback_numeric');

		$description = $this->input->post('description');
		$serialnumber = $this->input->post('serialnumber');
		$price = parse_decimals($this->input->post('price'));
		$quantity = parse_decimals($this->input->post('quantity'));
		$discount = parse_decimals($this->input->post('discount'));
		$item_location = $this->input->post('location');
		$discounted_total = $this->input->post('discounted_total') != '' ? $this->input->post('discounted_total') : NULL;

		if($this->form_validation->run() != FALSE)
		{
			$this->sale_lib->edit_item($item_id, $description, $serialnumber, $quantity, $discount, $price, $discounted_total);
		}
		else
		{
			$data['error'] = $this->lang->line('sales_error_editing_item');
		}

		$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($item_id), $item_location);

		$this->_reload($data);
	}

	public function delete_item($item_number)
	{
		$this->sale_lib->delete_item($item_number);

		$this->_reload();
	}

	public function remove_customer()
	{
		$this->sale_lib->clear_giftcard_remainder();
		$this->sale_lib->clear_rewards_remainder();
		$this->sale_lib->delete_payment($this->lang->line('sales_rewards'));
		$this->sale_lib->clear_invoice_number();
		$this->sale_lib->clear_quote_number();
		$this->sale_lib->remove_customer();

		$this->_reload();
	}

	public function complete()
	{
		$sale_id = $this->sale_lib->get_sale_id();
		$sale_type = $this->sale_lib->get_sale_type();
		$data = array();
		$data['dinner_table'] = $this->sale_lib->get_dinner_table();
		$data['cart'] = $this->sale_lib->get_cart();
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$data['transaction_date'] = date($this->config->item('dateformat'));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');
		$data['comments'] = $this->sale_lib->get_comment();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$employee_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_mode();
		$data['cur_giftcard_value'] = $this->sale_lib->get_giftcard_remainder();
		$data['cur_rewards_value'] = $this->sale_lib->get_rewards_remainder();
		$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
		$data['price_work_orders'] = $this->sale_lib->is_price_work_orders();
		$data['email_receipt'] = $this->sale_lib->is_email_receipt();
		$customer_id = $this->sale_lib->get_customer();
		$invoice_number_enabled = $this->sale_lib->get_invoice_number_enabled();
		$invoice_number = $this->sale_lib->get_invoice_number();
		$data["invoice_number"] = $invoice_number;
		$work_order_number = $this->sale_lib->get_work_order_number();
		$data["work_order_number"] = $work_order_number;
		$quote_number = $this->sale_lib->get_quote_number();
		$data["quote_number"] = $quote_number;
		$customer_info = $this->_load_customer_data($customer_id, $data);
		if($customer_info != NULL)
		{
			$data["customer_comments"] = $customer_info->comments;
		}
		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['payments'] = $this->sale_lib->get_payments();

		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals();
		$data['subtotal'] = $totals['subtotal'];
		$data['total'] = $totals['total'];
		$data['payments_total'] = $totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');
		$data['prediscount_subtotal'] = $totals['prediscount_subtotal'];
		$data['cash_total'] = $totals['cash_total'];
		$data['non_cash_total'] = $totals['total'];
		$data['cash_amount_due'] = $totals['cash_amount_due'];
		$data['non_cash_amount_due'] = $totals['amount_due'];

		if($data['cash_rounding'])
		{
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		}
		else
		{
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
		$data['amount_change'] = $data['amount_due'] * -1;

		$data['print_price_info'] = TRUE;

		$override_invoice_number = NULL;

		if($this->sale_lib->is_sale_by_receipt_mode() && $invoice_number_enabled )
		{
			$pos_invoice = TRUE;
			$candidate_invoice_number = $invoice_number;
			if($candidate_invoice_number != NULL && strlen($candidate_invoice_number) > 3)
			{
				if(strpos($candidate_invoice_number, '{') == FALSE)
				{
					$override_invoice_number = $candidate_invoice_number;
				}
			}
		}
		else
		{
			$pos_invoice = FALSE;
		}

		if($this->sale_lib->is_invoice_mode() || $pos_invoice)
		{
			// generate final invoice number (if using the invoice in sales by receipt mode then the invoice number can be manually entered or altered in some way
			if($pos_invoice)
			{
				// The user can retain the default encoded format or can manually override it.  It still passes through the rendering step.
				$this->sale_lib->set_invoice_number($this->input->post('invoice_number'), $keep_custom = TRUE);
				$invoice_format = $this->sale_lib->get_invoice_number();
				// If the user blanks out the invoice number and doesn't put anything in there then revert back to the default format encoding
				if(empty($invoice_format))
				{
					$invoice_format = $this->config->item('sales_invoice_format');
				}
			}
			else
			{
				$invoice_format = $this->config->item('sales_invoice_format');
			}

			if($override_invoice_number == NULL)
			{
				$invoice_number = $this->token_lib->render($invoice_format);
			}
			else
			{
				$invoice_number = $override_invoice_number;
			}

			if($sale_id == -1 && $this->Sale->check_invoice_number_exists($invoice_number))
			{
				$data['error'] = $this->lang->line('sales_invoice_number_duplicate');
				$this->_reload($data);
			}
			else
			{
				$data['invoice_number'] = $invoice_number;
				$data['sale_status'] = COMPLETED;
				$sale_type = SALE_TYPE_INVOICE;

				// Save the data to the sales table
				$data['sale_id_num'] = $this->Sale->save($sale_id, $data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], $invoice_number, $work_order_number, $quote_number, $sale_type, $data['payments'], $data['dinner_table'], $data['taxes']);
				$data['sale_id'] = 'POS ' . $data['sale_id_num'];

				// Resort and filter cart lines for printing
				$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);

				$data = $this->xss_clean($data);

				if($data['sale_id_num'] == -1)
				{
					$data['error_message'] = $this->lang->line('sales_transaction_failed');
				}
				else
				{
					$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);
					$this->load->view('sales/invoice', $data);
					$this->sale_lib->clear_all();
				}
			}
		}
		elseif($this->sale_lib->is_work_order_mode())
		{

			if(!($data['price_work_orders'] == 1))
			{
				$data['print_price_info'] = FALSE;
			}

			$data['sales_work_order'] = $this->lang->line('sales_work_order');
			$data['work_order_number_label'] = $this->lang->line('sales_work_order_number');

			if($work_order_number == NULL)
			{
				// generate work order number
				$work_order_format = $this->config->item('work_order_format');
				$work_order_number = $this->token_lib->render($work_order_format);
			}

			if($sale_id == -1 && $this->Sale->check_work_order_number_exists($work_order_number))
			{
				$data['error'] = $this->lang->line('sales_work_order_number_duplicate');
				$this->_reload($data);
			}
			else
			{
				$data['work_order_number'] = $work_order_number;
				$data['sale_status'] = SUSPENDED;
				$sale_type = SALE_TYPE_WORK_ORDER;

				$data['sale_id_num'] = $this->Sale->save($sale_id, $data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], $invoice_number, $work_order_number, $quote_number, $sale_type, $data['payments'], $data['dinner_table'], $data['taxes']);
				$this->sale_lib->set_suspended_id($data['sale_id_num']);

				$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);

				$data = $this->xss_clean($data);

				$data['barcode'] = NULL;

				$this->load->view('sales/work_order', $data);
				$this->sale_lib->clear_mode();
				$this->sale_lib->clear_all();
			}
		}
		elseif($this->sale_lib->is_quote_mode())
		{
			$data['sales_quote'] = $this->lang->line('sales_quote');
			$data['quote_number_label'] = $this->lang->line('sales_quote_number');

			if($quote_number == NULL)
			{
				// generate quote number
				$quote_format = $this->config->item('sales_quote_format');
				$quote_number = $this->token_lib->render($quote_format);
			}

			if($sale_id == -1 && $this->Sale->check_quote_number_exists($quote_number))
			{
				$data['error'] = $this->lang->line('sales_quote_number_duplicate');
				$this->_reload($data);
			}
			else
			{
				$data['quote_number'] = $quote_number;
				$data['sale_status'] = SUSPENDED;
				$sale_type = SALE_TYPE_QUOTE;

				$data['sale_id_num'] = $this->Sale->save($sale_id, $data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], $invoice_number, $work_order_number, $quote_number, $sale_type, $data['payments'], $data['dinner_table'], $data['taxes']);
				$this->sale_lib->set_suspended_id($data['sale_id_num']);

				$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);

				$data = $this->xss_clean($data);

				$data['barcode'] = NULL;

				$this->load->view('sales/quote', $data);
				$this->sale_lib->clear_mode();
				$this->sale_lib->clear_all();
			}
		}
		else
		{
			// Save the data to the sales table
			$data['sale_status'] = COMPLETED;
			if($this->sale_lib->is_return_mode())
			{
				$sale_type = SALE_TYPE_RETURN;
			}
			else
			{
				$sale_type = SALE_TYPE_POS;
			}

			$data['sale_id_num'] = $this->Sale->save($sale_id, $data['sale_status'], $data['cart'], $customer_id, $employee_id, $data['comments'], $invoice_number, $work_order_number, $quote_number, $sale_type, $data['payments'], $data['dinner_table'], $data['taxes']);

			$data['sale_id'] = 'POS ' . $data['sale_id_num'];

			$data['cart'] = $this->sale_lib->sort_and_filter_cart($data['cart']);
			$data = $this->xss_clean($data);

			if($data['sale_id_num'] == -1)
			{
				$data['error_message'] = $this->lang->line('sales_transaction_failed');
			}
			else
			{
				$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);

				// Reload (sorted) and filter the cart line items for printing purposes
				$data['cart'] = $this->get_filtered($this->sale_lib->get_cart_reordered($data['sale_id_num']));

				$this->load->view('sales/receipt', $data);
				$this->sale_lib->clear_all();
			}
		}
	}

	public function send_pdf($sale_id, $type = 'invoice')
	{
		$sale_data = $this->_load_sale_data($sale_id);

		$result = FALSE;
		$message = $this->lang->line('sales_invoice_no_email');

		if(!empty($sale_data['customer_email']))
		{
			$to = $sale_data['customer_email'];
			$number = $sale_data[$type."_number"];
			$subject = $this->lang->line("sales_$type") . ' ' . $number;

			$text = $this->config->item('invoice_email_message');
			$tokens = array(new Token_invoice_sequence($sale_data['invoice_number']),
				new Token_invoice_count('POS ' . $sale_data['sale_id']),
				new Token_customer((object)$sale_data));
			$text = $this->token_lib->render($text, $tokens);

			// generate email attachment: invoice in pdf format
			$html = $this->load->view("sales/" . $type . "_email", $sale_data, TRUE);
			// load pdf helper
			$this->load->helper(array('dompdf', 'file'));
			$filename = sys_get_temp_dir() . '/' . $this->lang->line("sales_$type") . '-' . str_replace('/', '-', $number) . '.pdf';
			if(file_put_contents($filename, pdf_create($html)) !== FALSE)
			{
				$result = $this->email_lib->sendEmail($to, $subject, $text, $filename);
			}

			$message = $this->lang->line($result ? "sales_" . $type . "_sent" : "sales_" . $type . "_unsent") . ' ' . $to;
		}

		echo json_encode(array('success' => $result, 'message' => $message, 'id' => $sale_id));

		$this->sale_lib->clear_all();

		return $result;
	}

	public function send_receipt($sale_id)
	{
		$sale_data = $this->_load_sale_data($sale_id);

		$result = FALSE;
		$message = $this->lang->line('sales_receipt_no_email');

		if(!empty($sale_data['customer_email']))
		{
			$sale_data['barcode'] = $this->barcode_lib->generate_receipt_barcode($sale_data['sale_id']);

			$to = $sale_data['customer_email'];
			$subject = $this->lang->line('sales_receipt');

			$text = $this->load->view('sales/receipt_email', $sale_data, TRUE);

			$result = $this->email_lib->sendEmail($to, $subject, $text);

			$message = $this->lang->line($result ? 'sales_receipt_sent' : 'sales_receipt_unsent') . ' ' . $to;
		}

		echo json_encode(array('success' => $result, 'message' => $message, 'id' => $sale_id));

		$this->sale_lib->clear_all();

		return $result;
	}

	private function _load_customer_data($customer_id, &$data, $stats = FALSE)
	{
		$customer_info = '';

		if($customer_id != -1)
		{
			$customer_info = $this->Customer->get_info($customer_id);
			$data['customer_id'] = $customer_id;
			if(!empty($customer_info->company_name))
			{
				$data['customer'] = $customer_info->company_name;
			}
			else
			{
				$data['customer'] = $customer_info->first_name . ' ' . $customer_info->last_name;
			}
			$data['first_name'] = $customer_info->first_name;
			$data['last_name'] = $customer_info->last_name;
			$data['customer_email'] = $customer_info->email;
			$data['customer_address'] = $customer_info->address_1;
			if(!empty($customer_info->zip) || !empty($customer_info->city))
			{
				$data['customer_location'] = $customer_info->zip . ' ' . $customer_info->city;
			}
			else
			{
				$data['customer_location'] = '';
			}
			$data['customer_account_number'] = $customer_info->account_number;
			$data['customer_discount_percent'] = $customer_info->discount_percent;
			$package_id = $this->Customer->get_info($customer_id)->package_id;
			if($package_id != NULL)
			{
				$package_name = $this->Customer_rewards->get_name($package_id);
				$points = $this->Customer->get_info($customer_id)->points;
				$data['customer_rewards']['package_id'] = $package_id;
				$data['customer_rewards']['points'] = empty($points) ? 0 : $points;
				$data['customer_rewards']['package_name'] = $package_name;
			}

			if($stats)
			{
				$cust_stats = $this->Customer->get_stats($customer_id);
				$data['customer_total'] = empty($cust_stats) ? 0 : $cust_stats->total;
			}

			$data['customer_info'] = implode("\n", array(
				$data['customer'],
				$data['customer_address'],
				$data['customer_location'],
				$data['customer_account_number']
			));
		}

		return $customer_info;
	}

	private function _load_sale_data($sale_id)
	{
		$this->sale_lib->clear_all();
		$this->sale_lib->reset_cash_flags();
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$this->sale_lib->copy_entire_sale($sale_id);
		$data = array();
		$data['cart'] = $this->sale_lib->get_cart();
		$data['payments'] = $this->sale_lib->get_payments();
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();

		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($sale_info['sale_time']));
		$data['transaction_date'] = date($this->config->item('dateformat'), strtotime($sale_info['sale_time']));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');


		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals();
		$data['subtotal'] = $totals['subtotal'];
		$data['total'] = $totals['total'];
		$data['payments_total'] = $totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');
		$data['prediscount_subtotal'] = $totals['prediscount_subtotal'];
		$data['cash_total'] = $totals['cash_total'];
		$data['non_cash_total'] = $totals['total'];
		$data['cash_amount_due'] = $totals['cash_amount_due'];
		$data['non_cash_amount_due'] = $totals['amount_due'];

		if($this->session->userdata('cash_rounding'))
		{
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		}
		else
		{
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
		$data['amount_change'] = $data['amount_due'] * -1;

		$employee_info = $this->Employee->get_info($this->sale_lib->get_employee());
		$data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name[0];
		$this->_load_customer_data($this->sale_lib->get_customer(), $data);

		$data['sale_id_num'] = $sale_id;
		$data['sale_id'] = 'POS ' . $sale_id;
		$data['comments'] = $sale_info['comment'];
		$data['invoice_number'] = $sale_info['invoice_number'];
		$data['quote_number'] = $sale_info['quote_number'];
		$data['sale_status'] = $sale_info['sale_status'];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);
		$data['print_after_sale'] = FALSE;
		$data['price_work_orders'] = FALSE;

		if($this->sale_lib->get_mode() == 'sale_invoice')
		{
			$data['mode_label'] = $this->lang->line('sales_invoice');
			$data['customer_required'] = $this->lang->line('sales_customer_required');
		}
		elseif($this->sale_lib->get_mode() == 'sale_quote')
		{
			$data['mode_label'] = $this->lang->line('sales_quote');
			$data['customer_required'] = $this->lang->line('sales_customer_required');
		}
		elseif($this->sale_lib->get_mode() == 'sale_work_order')
		{
			$data['mode_label'] = $this->lang->line('sales_work_order');
			$data['customer_required'] = $this->lang->line('sales_customer_required');
		}
		elseif($this->sale_lib->get_mode() == 'return')
		{
			$data['mode_label'] = $this->lang->line('sales_return');
			$data['customer_required'] = $this->lang->line('sales_customer_optional');
		}
		else
		{
			$data['mode_label'] = $this->lang->line('sales_receipt');
			$data['customer_required'] = $this->lang->line('sales_customer_optional');
		}

		return $this->xss_clean($data);
	}

	private function _reload($data = array())
	{
		$sale_id = $this->session->userdata('sale_id');
		if($sale_id == '')
		{
			$sale_id = -1;
			$this->session->set_userdata('sale_id', -1);
		}
		$data['cart'] = $this->sale_lib->get_cart();
		$customer_info = $this->_load_customer_data($this->sale_lib->get_customer(), $data, TRUE);

		$data['modes'] = $this->sale_lib->get_register_mode_options();
		$data['mode'] = $this->sale_lib->get_mode();
		$data['empty_tables'] = $this->sale_lib->get_empty_tables();
		$data['selected_table'] = $this->sale_lib->get_dinner_table();
		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('sales');
		$data['stock_location'] = $this->sale_lib->get_sale_location();
		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);
		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['payments'] = $this->sale_lib->get_payments();
		// sale_type (0=pos, 1=invoice, 2=work order, 3=quote, 4=return)
		$sale_type = $this->sale_lib->get_sale_type();

		// Returns 'subtotal', 'total', 'cash_total', 'payment_total', 'amount_due', 'cash_amount_due', 'payments_cover_total'
		$totals = $this->sale_lib->get_totals();
		$data['item_count'] = $totals['item_count'];
		$data['total_units'] = $totals['total_units'];
		$data['subtotal'] = $totals['subtotal'];
		$data['total'] = $totals['total'];
		$data['payments_total'] = $totals['payment_total'];
		$data['payments_cover_total'] = $totals['payments_cover_total'];
		$data['cash_rounding'] = $this->session->userdata('cash_rounding');
		$data['prediscount_subtotal'] = $totals['prediscount_subtotal'];
		$data['cash_total'] = $totals['cash_total'];
		$data['non_cash_total'] = $totals['total'];
		$data['cash_amount_due'] = $totals['cash_amount_due'];
		$data['non_cash_amount_due'] = $totals['amount_due'];

		if($data['cash_rounding'])
		{
			$data['total'] = $totals['cash_total'];
			$data['amount_due'] = $totals['cash_amount_due'];
		}
		else
		{
			$data['total'] = $totals['total'];
			$data['amount_due'] = $totals['amount_due'];
		}
		$data['amount_change'] = $data['amount_due'] * -1;

		$data['comment'] = $this->sale_lib->get_comment();
		$data['email_receipt'] = $this->sale_lib->is_email_receipt();
		$data['selected_payment_type'] = $this->sale_lib->get_payment_type();
		if($customer_info && $this->config->item('customer_reward_enable') == TRUE)
		{
			$data['payment_options'] = $this->Sale->get_payment_options(TRUE, TRUE);
		}
		else
		{
			$data['payment_options'] = $this->Sale->get_payment_options();
		}

		$data['items_module_allowed'] = $this->Employee->has_grant('items', $this->Employee->get_logged_in_employee_info()->person_id);

		$invoice_format = $this->config->item('sales_invoice_format');
		$data['invoice_format'] = $invoice_format;

		$this->set_invoice_number($invoice_format);
		$data['invoice_number'] = $invoice_format;

		$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_mode();
		$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
		$data['price_work_orders'] = $this->sale_lib->is_price_work_orders();

		$data['pos_mode'] = $data['mode'] == 'sale' || $data['mode'] == 'return';

		$data['quote_number'] = $this->sale_lib->get_quote_number();
		$data['work_order_number'] = $this->sale_lib->get_work_order_number();

		if($this->sale_lib->get_mode() == 'sale_invoice')
		{
			$data['mode_label'] = $this->lang->line('sales_invoice');
			$data['customer_required'] = $this->lang->line('sales_customer_required');
		}
		elseif($this->sale_lib->get_mode() == 'sale_quote')
		{
			$data['mode_label'] = $this->lang->line('sales_quote');
			$data['customer_required'] = $this->lang->line('sales_customer_required');
		}
		elseif($this->sale_lib->get_mode() == 'sale_work_order')
		{
			$data['mode_label'] = $this->lang->line('sales_work_order');
			$data['customer_required'] = $this->lang->line('sales_customer_required');
		}
		elseif($this->sale_lib->get_mode() == 'return')
		{
			$data['mode_label'] = $this->lang->line('sales_return');
			$data['customer_required'] = $this->lang->line('sales_customer_optional');
		}
		else
		{
			$data['mode_label'] = $this->lang->line('sales_receipt');
			$data['customer_required'] = $this->lang->line('sales_customer_optional');
		}

		$data = $this->xss_clean($data);

		$this->load->view("sales/register", $data);
	}

	public function receipt($sale_id)
	{
		$data = $this->_load_sale_data($sale_id);
		$this->load->view('sales/receipt', $data);
		$this->sale_lib->clear_all();
	}

	public function invoice($sale_id)
	{
		$data = $this->_load_sale_data($sale_id);
		$this->load->view('sales/invoice', $data);
		$this->sale_lib->clear_all();
	}

	public function edit($sale_id)
	{
		$data = array();

		$data['employees'] = array();
		foreach($this->Employee->get_all()->result() as $employee)
		{
			foreach(get_object_vars($employee) as $property => $value)
			{
				$employee->$property = $this->xss_clean($value);
			}

			$data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
		}

		$sale_info = $this->xss_clean($this->Sale->get_info($sale_id)->row_array());
		$data['selected_customer_name'] = $sale_info['customer_name'];
		$data['selected_customer_id'] = $sale_info['customer_id'];
		$data['sale_info'] = $sale_info;

		$data['payments'] = array();
		foreach($this->Sale->get_sale_payments($sale_id)->result() as $payment)
		{
			foreach(get_object_vars($payment) as $property => $value)
			{
				$payment->$property = $this->xss_clean($value);
			}
			$data['payments'][] = $payment;
		}

		// don't allow gift card to be a payment option in a sale transaction edit because it's a complex change
		$data['payment_options'] = $this->xss_clean($this->Sale->get_payment_options(FALSE));
		$this->load->view('sales/form', $data);
	}

	public function delete($sale_id = -1, $update_inventory = TRUE)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$has_grant = $this->Employee->has_grant('sales_delete', $employee_id);

		if(!$has_grant)
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_not_authorized')));
		}
		else
		{
			$sale_ids = $sale_id == -1 ? $this->input->post('ids') : array($sale_id);

			if($this->Sale->delete_list($sale_ids, $employee_id, $update_inventory))
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_deleted') . ' ' .
					count($sale_ids) . ' ' . $this->lang->line('sales_one_or_multiple'), 'ids' => $sale_ids));
			}
			else
			{
				echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_deleted')));
			}
		}
	}

	public function restore($sale_id = -1, $update_inventory = TRUE)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$has_grant = $this->Employee->has_grant('sales_delete', $employee_id);

		if(!$has_grant)
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_not_authorized')));
		}
		else
		{
			$sale_ids = $sale_id == -1 ? $this->input->post('ids') : array($sale_id);

			if($this->Sale->restore_list($sale_ids, $employee_id, $update_inventory))
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_restored') . ' ' .
					count($sale_ids) . ' ' . $this->lang->line('sales_one_or_multiple'), 'ids' => $sale_ids));
			}
			else
			{
				echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_restored')));
			}
		}
	}

	/**
	 * This saves the sale from the update sale view (sales/form).
	 * It only updates the sales table and payments.
	 * @param int $sale_id
	 */
	public function save($sale_id = -1)
	{
		$newdate = $this->input->post('date');

		$date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);

		$sale_data = array(
			'sale_time' => $date_formatter->format('Y-m-d H:i:s'),
			'customer_id' => $this->input->post('customer_id') != '' ? $this->input->post('customer_id') : NULL,
			'employee_id' => $this->input->post('employee_id'),
			'comment' => $this->input->post('comment'),
			'invoice_number' => $this->input->post('invoice_number') != '' ? $this->input->post('invoice_number') : NULL
		);

		// go through all the payment type input from the form, make sure the form matches the name and iterator number
		$payments = array();
		$number_of_payments = $this->input->post('number_of_payments');
		for($i = 0; $i < $number_of_payments; ++$i)
		{
			$payment_amount = $this->input->post('payment_amount_' . $i);
			$payment_type = $this->input->post('payment_type_' . $i);
			// remove any 0 payment if by mistake any was introduced at sale time
			if($payment_amount != 0)
			{
				// search for any payment of the same type that was already added, if that's the case add up the new payment amount
				$key = FALSE;
				if(!empty($payments))
				{
					// search in the multi array the key of the entry containing the current payment_type
					// NOTE: in PHP5.5 the array_map could be replaced by an array_column
					$key = array_search($payment_type, array_map(function ($v)
					{
						return $v['payment_type'];
					}, $payments));
				}

				// if no previous payment is found add a new one
				if($key === FALSE)
				{
					$payments[] = array('payment_type' => $payment_type, 'payment_amount' => $payment_amount);
				}
				else
				{
					// add up the new payment amount to an existing payment type
					$payments[$key]['payment_amount'] += $payment_amount;
				}
			}
		}

		if($this->Sale->update($sale_id, $sale_data, $payments))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('sales_successfully_updated'), 'id' => $sale_id));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('sales_unsuccessfully_updated'), 'id' => $sale_id));
		}
	}

	/**
	 * This is used to cancel a suspended pos sale, quote.
	 * Completed sales (POS Sales or Invoiced Sales) can not be removed from the system
	 * Work orders can be canceled but are not physically removed from the sales history
	 */
	public function cancel()
	{
		$sale_id = $this->sale_lib->get_sale_id();
		if($sale_id != -1 && $sale_id != '')
		{
			$sale_type = $this->sale_lib->get_sale_type();
			if($sale_type == SALE_TYPE_WORK_ORDER)
			{
				$this->Sale->update_sale_status($sale_id, CANCELED);
			}
			else
			{
				$this->Sale->delete($sale_id, FALSE);
				$this->session->set_userdata('sale_id', -1);
			}
		}

		$this->sale_lib->clear_all();
		$this->_reload();
	}

	public function discard_suspended_sale()
	{
		$suspended_id = $this->sale_lib->get_suspended_id();
		$this->sale_lib->clear_all();
		$this->Sale->delete_suspended_sale($suspended_id);
		$this->_reload();
	}

	/**
	 * Suspend the current sale.
	 * If the current sale is already suspended then update the existing suspended sale.
	 * Otherwise create it as a new suspended sale
	 */
	public function suspend()
	{
		$mode = $this->sale_lib->get_mode();
		$sale_id = $this->sale_lib->get_sale_id();
		$dinner_table = $this->sale_lib->get_dinner_table();
		$cart = $this->sale_lib->get_cart();
		$payments = $this->sale_lib->get_payments();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$customer_id = $this->sale_lib->get_customer();
		$customer_info = $this->Customer->get_info($customer_id);
		$invoice_number = $this->sale_lib->get_invoice_number();
		$work_order_number = $this->sale_lib->get_work_order_number();
		$quote_number = $this->sale_lib->get_quote_number();
		$sale_id = $this->sale_lib->get_sale_id();
		$sale_type = $this->sale_lib->get_sale_type();
		if($sale_type == '')
		{
			$sale_type = SALE_TYPE_POS;
		}
		$comment = $this->sale_lib->get_comment();
		$sale_status = SUSPENDED;

		$data = array();
		$sales_taxes = array();
		if($this->Sale->save($sale_id, $sale_status, $cart, $customer_id, $employee_id, $comment, $invoice_number, $work_order_number, $quote_number, $sale_type, $payments, $dinner_table, $sales_taxes) == '-1')
		{
			$data['error'] = $this->lang->line('sales_unsuccessfully_suspended_sale');
		}
		else
		{
			$data['success'] = $this->lang->line('sales_successfully_suspended_sale');
		}

		$this->sale_lib->clear_all();
		$this->_reload($data);
	}

	/**
	 * List suspended sales
	 */
	public function suspended()
	{
		$customer_id = $this->sale_lib->get_customer();
		$data = array();
		$data['suspended_sales'] = $this->xss_clean($this->Sale->get_all_suspended($customer_id));
		$data['dinner_table_enable'] = $this->config->item('dinner_table_enable');
		$this->load->view('sales/suspended', $data);
	}

	/*
	 * Unsuspended sales are now left in the tables and are only removed
	 * when they are intentionally cancelled.
	 */
	public function unsuspend()
	{
		$sale_id = $this->input->post('suspended_sale_id');
		$this->sale_lib->clear_all();

		if($sale_id > 0)
		{
			$this->sale_lib->copy_entire_sale($sale_id);
		}

		// Set current register mode to reflect that of unsuspended order type
		$this->change_register_mode($this->sale_lib->get_sale_type());

		$this->_reload();
	}

	public function check_invoice_number()
	{
		$sale_id = $this->input->post('sale_id');
		$invoice_number = $this->input->post('invoice_number');
		$exists = !empty($invoice_number) && $this->Sale->check_invoice_number_exists($invoice_number, $sale_id);
		echo !$exists ? 'true' : 'false';
	}

	public function get_filtered($cart)
	{
		$filtered_cart = array();
		foreach($cart as $id => $item)
		{
			if($item['print_option'] == PRINT_ALL) // always include
			{
				$filtered_cart[$id] = $item;
			}
			elseif($item['print_option'] == PRINT_PRICED && $item['price'] != 0)  // include only if the price is not zero
			{
				$filtered_cart[$id] = $item;
			}
			// print_option 2 is never included
		}

		return $filtered_cart;
	}
}

?>
