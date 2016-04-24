<?php
require_once ("Secure_area.php");

class Sales extends Secure_area
{
	function __construct()
	{
		parent::__construct('sales');
		$this->load->library('sale_lib');
		$this->load->library('barcode_lib');
	}

	function index()
	{
		$this->_reload();
	}
	
	function manage()
	{
		$person_id = $this->session->userdata('person_id');

		if (!$this->Employee->has_grant('reports_sales', $person_id))
		{
			redirect('no_access/sales/reports_sales');
		}
		else
		{
			$data['controller_name'] = $this->get_controller_name();
			$data['table_headers'] = get_sales_manage_table_headers();

			// filters that will be loaded in the multiselect dropdown
			if($this->config->item('invoice_enable') == TRUE)
			{
				$data['filters'] = array('only_cash' => $this->lang->line('sales_cash_filter'),
										'only_invoices' => $this->lang->line('sales_invoice_filter'));
			}
			else
			{
				$data['filters'] = array('only_cash' => $this->lang->line('sales_cash_filter'));
			}

			$this->load->view($data['controller_name'] . '/manage', $data);
		}
	}
	
	function get_row($row_id)
	{
		$this->Sale->create_sales_items_temp_table();

		$sale_info = $this->Sale->get_info($row_id)->result_array();
		$data_row = get_sales_manage_sale_data_row($sale_info[0], $this);

		echo $data_row;
	}
	
	/*
	Returns Sales table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$this->Sale->create_sales_items_temp_table();

		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');

		$is_valid_receipt = isset($search) ? $this->sale_lib->is_valid_receipt($search) : FALSE;

		$filters = array('sale_type' => 'all',
			'location_id' => 'all',
			'start_date' => $this->input->get('start_date'),
			'end_date' => $this->input->get('end_date'),
			'only_cash' => FALSE,
			'only_invoices' => $this->config->item('invoice_enable') && $this->input->get('only_invoices'),
			'is_valid_receipt' => $is_valid_receipt);


			

		// check if any filter is set in the multiselect dropdown
		$filledup = array_fill_keys($this->input->get('filters'), true);
		$filters = array_merge($filters, $filledup);

		$sales = $this->Sale->search($search, $filters, $offset, $limit);
		$total_rows = $this->Sale->get_found_rows($search, $filters);
		$payments = $this->Sale->get_payments_summary($search, $filters);
		$payment_summary = get_sales_manage_payments_summary($payments, $sales, $this);

		$data_rows = array();
		foreach($sales->result() as $sale)
		{
			$data_rows[] = get_sale_data_row($sale, $this);
		}
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows,'payment_summary' => $payment_summary));
	}

	function item_search()
	{
		$suggestions = array();
		$search = $this->input->get('term') != '' ? $this->input->get('term') : null;

		if ($this->sale_lib->get_mode() == 'return' && $this->sale_lib->is_valid_receipt($search) )
		{
			$suggestions[] = $search;
		}
		$suggestions = array_merge($suggestions, $this->Item->get_search_suggestions($search));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_search_suggestions($search));

		echo json_encode($suggestions);
	}

	function suggest_search()
	{
		$search = $this->input->post('term') != '' ? $this->input->post('term') : null;
		
		$suggestions = $this->Sale->get_search_suggestions($search);
		
		echo json_encode($suggestions);
	}

	function select_customer()
	{
		$customer_id = $this->input->post('customer');
		if ($this->Customer->exists($customer_id))
		{
			$this->sale_lib->set_customer($customer_id);
		}
		$this->_reload();
	}

	function change_mode()
	{
		$stock_location = $this->input->post("stock_location");
		if (!$stock_location || $stock_location == $this->sale_lib->get_sale_location())
		{
			$mode = $this->input->post("mode");
			$this->sale_lib->set_mode($mode);
		} 
		else if ($this->Stock_location->is_allowed_location($stock_location, 'sales'))
		{
			$this->sale_lib->set_sale_location($stock_location);
		}

		$this->_reload();
	}
	
	function set_comment() 
	{
		$this->sale_lib->set_comment($this->input->post('comment'));
	}
	
	function set_invoice_number()
	{
		$this->sale_lib->set_invoice_number($this->input->post('sales_invoice_number'));
	}
	
	function set_invoice_number_enabled()
	{
		$this->sale_lib->set_invoice_number_enabled($this->input->post('sales_invoice_number_enabled'));
	}
	
	function set_print_after_sale()
	{
		$this->sale_lib->set_print_after_sale($this->input->post('sales_print_after_sale'));
	}
	
	function set_email_receipt()
	{
 		$this->sale_lib->set_email_receipt($this->input->post('email_receipt'));
	}

	// Multiple Payments
	function add_payment()
	{		
		$data = array();
		$this->form_validation->set_rules('amount_tendered', 'lang:sales_amount_tendered', 'trim|required|numeric');
		
		if ( $this->form_validation->run() == FALSE )
		{
			if ( $this->input->post('payment_type') == $this->lang->line('sales_gift_card') )
			{
				$data['error']=$this->lang->line('sales_must_enter_numeric_giftcard');
			}
			else
			{
				$data['error']=$this->lang->line('sales_must_enter_numeric');
			}
				
 			$this->_reload( $data );

 			return;
		}
		
		$payment_type = $this->input->post('payment_type');
		if ( $payment_type == $this->lang->line('sales_giftcard') )
		{
			$payments = $this->sale_lib->get_payments();
			$payment_type = $this->input->post('payment_type') . ':' . $payment_amount = $this->input->post('amount_tendered');
			$current_payments_with_giftcard = isset($payments[$payment_type]) ? $payments[$payment_type]['payment_amount'] : 0;
			$cur_giftcard_value = $this->Giftcard->get_giftcard_value($this->input->post('amount_tendered')) - $current_payments_with_giftcard;
			
			if ( $cur_giftcard_value <= 0 )
			{
				$data['error'] = $this->lang->line('giftcards_remaining_balance', $this->input->post('amount_tendered'), to_currency( $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered'))));
				$this->_reload( $data );
				return;
			}
			$new_giftcard_value = $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) - $this->sale_lib->get_amount_due();
			$new_giftcard_value = ( $new_giftcard_value >= 0 ) ? $new_giftcard_value : 0;
			$this->sale_lib->set_giftcard_remainder($new_giftcard_value);
			$data['warning'] = $this->lang->line('giftcards_remaining_balance', $this->input->post('amount_tendered'), to_currency( $new_giftcard_value, TRUE ));
			$payment_amount = min( $this->sale_lib->get_amount_due(), $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) );
		}
		else
		{
			$payment_amount = $this->input->post('amount_tendered');
		}
		
		if( !$this->sale_lib->add_payment( $payment_type, $payment_amount ) )
		{
			$data['error'] = 'Unable to Add Payment! Please try again!';
		}
		
		$this->_reload($data);
	}

	// Multiple Payments
	function delete_payment( $payment_id )
	{
		$this->sale_lib->delete_payment( $payment_id );

		$this->_reload();
	}

	function add()
	{
		$data = array();

		$mode = $this->sale_lib->get_mode();
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post('item');
		$quantity = ($mode == "return") ? -1 : 1;
		$item_location = $this->sale_lib->get_sale_location();

		$discount = 0;
		
		// check if any discount is assigned to the selected customer
		$customer_id = $this->sale_lib->get_customer();
		if($customer_id != -1)
		{
			// load the customer discount if any
			$discount = $this->Customer->get_info($customer_id)->discount_percent == '' ? 0 : $this->Customer->get_info($customer_id)->discount_percent;
		}
		
		// if the customer discount is 0 or no customer is selected apply the default sales discount
		if($discount == 0)
		{
			$discount = $this->config->item('default_sales_discount');
		}
		
		if($mode == 'return' && $this->sale_lib->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->sale_lib->return_entire_sale($item_id_or_number_or_item_kit_or_receipt);
		}
		else if($this->sale_lib->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->sale_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt, $item_location);
		}
		else if(!$this->sale_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $quantity, $item_location, $discount))
		{
			$data['error'] = $this->lang->line('sales_unable_to_add_item');
		}
		
		$data['warning'] = $this->sale_lib->out_of_stock($item_id_or_number_or_item_kit_or_receipt, $item_location);

		$this->_reload($data);
	}

	function edit_item($line)
	{
		$data = array();

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|numeric');
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|numeric');
		$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|numeric');

		$description = $this->input->post('description');
		$serialnumber = $this->input->post('serialnumber');
		$price = $this->input->post('price');
		$quantity = $this->input->post('quantity');
		$discount = $this->input->post('discount');
		$item_location = $this->input->post('location');

		if ($this->form_validation->run() != FALSE)
		{
			$this->sale_lib->edit_item($line, $description, $serialnumber, $quantity, $discount, $price);
		}
		else
		{
			$data['error'] = $this->lang->line('sales_error_editing_item');
		}
		$data['warning'] = $this->sale_lib->out_of_stock($this->sale_lib->get_item_id($line),$item_location);

		$this->_reload($data);
	}

	function delete_item($item_number)
	{
		$this->sale_lib->delete_item($item_number);

		$this->_reload();
	}

	function remove_customer()
	{
		$this->sale_lib->clear_giftcard_remainder();
		$this->sale_lib->clear_invoice_number();
		$this->sale_lib->remove_customer();

		$this->_reload();
	}

	function complete()
	{
		$data['cart'] = $this->sale_lib->get_cart();
		$data['subtotal'] = $this->sale_lib->get_subtotal();
		$data['discounted_subtotal'] = $this->sale_lib->get_subtotal(TRUE);
		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);
		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['total'] = $this->sale_lib->get_total();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['receipt_title'] = $this->lang->line('sales_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat').' '.$this->config->item('timeformat'));
		$data['transaction_date'] = date($this->config->item('dateformat'));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');
		$data['comments'] = $this->sale_lib->get_comment();
		$data['payments'] = $this->sale_lib->get_payments();
		$data['amount_change'] = $this->sale_lib->get_amount_due() * -1;
		$data['amount_due'] = $this->sale_lib->get_amount_due();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$emp_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $emp_info->first_name  . ' ' . $emp_info->last_name;
		$data['company_info'] = implode("\n", array(
				$this->config->item('address'),
				$this->config->item('phone'),
				$this->config->item('account_number')
		));
		$customer_id = $this->sale_lib->get_customer();
		$cust_info = $this->_load_customer_data($customer_id, $data);
		$invoice_number = $this->_substitute_invoice_number($cust_info);
		if ($this->sale_lib->is_invoice_number_enabled() && $this->Sale->invoice_number_exists($invoice_number))
		{
			$data['error'] = $this->lang->line('sales_invoice_number_duplicate');
			$this->_reload($data);
		}
		else 
		{
			$invoice_number = $this->sale_lib->is_invoice_number_enabled() ? $invoice_number : null;
			$data['invoice_number'] = $invoice_number;
			$data['sale_id'] = 'POS ' . $this->Sale->save($data['cart'], $customer_id, $employee_id, $data['comments'], $invoice_number, $data['payments']);
			if ($data['sale_id'] == 'POS -1')
			{
				$data['error_message'] = $this->lang->line('sales_transaction_failed');
			}
			else
			{
				$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);
				// if we want to email. .. just attach the pdf in there?
				if ($this->sale_lib->get_email_receipt() && !empty($cust_info->email))
				{
					$this->load->library('email');
					$config['mailtype'] = 'html';				
					$this->email->initialize($config);
					$this->email->from($this->config->item('email'), $this->config->item('company'));
					$this->email->to($cust_info->email); 
	
					$this->email->subject($this->lang->line('sales_receipt'));
					if ($this->config->item('use_invoice_template') && $this->sale_lib->is_invoice_number_enabled())
					{
						$data['image_prefix'] = "";
						$filename = $this->_invoice_email_pdf($data);
						$this->email->attach($filename);
						$text = $this->config->item('invoice_email_message');
						$text = str_replace('$INV', $invoice_number, $text);
						$text = str_replace('$CO', $data['sale_id'], $text);
						$text = $this->_substitute_customer($text, $cust_info);
						$this->email->message($text);
					}
					else
					{
						$this->email->message($this->load->view("sales/receipt_email", $data, true));	
					}
					$this->email->send();
				}
			}
			$data['cur_giftcard_value'] = $this->sale_lib->get_giftcard_remainder();
			$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
			if ($this->sale_lib->is_invoice_number_enabled() && $this->config->item('use_invoice_template'))
			{
				$this->load->view("sales/invoice", $data);
			}
			else
			{
				$this->load->view("sales/receipt", $data);
			}

			$this->sale_lib->clear_all();
		}
	}
	
	private function _invoice_email_pdf($data)
	{
		$data['image_prefix'] = "";
		$html = $this->load->view('sales/invoice_email', $data, true);
		// load pdf helper
		$this->load->helper(array('dompdf', 'file'));
		$file_content  = pdf_create($html, '', false);
		$filename = sys_get_temp_dir() . '/'. $this->lang->line('sales_invoice') . '-' . str_replace('/', '-' , $data["invoice_number"]) . '.pdf';
		write_file($filename, $file_content);

		return $filename;
	}
	
	function invoice_email($sale_id)
	{
		$sale_data = $this->_load_sale_data($sale_id);
		$sale_data['image_prefix'] = base_url();
		$this->load->view('sales/invoice_email', $sale_data);
		$this->sale_lib->clear_all();
	}
	
	function send_invoice($sale_id)
	{
		$sale_data = $this->_load_sale_data($sale_id);
		$text = $this->config->item('invoice_email_message');
		$text = str_replace('$INV', $sale_data['invoice_number'], $text);
		$text = str_replace('$CO', 'POS ' . $sale_data['sale_id'], $text);
		$text = $this->_substitute_customer($text,(object) $sale_data);
		$result = FALSE;
		$message = $this->lang->line('sales_invoice_no_email');
		if (isset($sale_data["customer_email"]) && !empty( $sale_data["customer_email"])) {
			$this->load->library('email');
			$this->email->from($this->config->item('email'), $this->config->item('company'));
			$this->email->to($sale_data['customer_email']);
			$this->email->subject($this->lang->line('sales_invoice') . ' ' . $sale_data['invoice_number']);
			$this->email->message($text);
			$filename = $this->_invoice_email_pdf($sale_data);
			$this->email->attach($filename);
			$result = $this->email->send();
			$message = $this->lang->line($result ? 'sales_invoice_sent' : 'sales_invoice_unsent') . ' ' . $sale_data["customer_email"];
		}
		echo json_encode(array('success'=>$result, 'message'=>$message, 'id'=>$sale_id));
		$this->sale_lib->clear_all();
	}
	
	private function _substitute_variable($text, $variable, $object, $function)
	{
		// don't query if this variable isn't used
		if (strstr($text, $variable))
		{
			$value = call_user_func(array($object, $function));
			$text = str_replace($variable, $value, $text);
		}

		return $text;
	}
	
	private function _substitute_customer($text, $cust_info)
	{
		// substitute customer info
		$customer_id = $this->sale_lib->get_customer();
		if($customer_id != -1 && $cust_info != '')
		{
			$text = str_replace('$CU',$cust_info->first_name . ' ' . $cust_info->last_name,$text);
			$words = preg_split("/\s+/", trim($cust_info->first_name . ' ' . $cust_info->last_name));
			$acronym = "";
			foreach ($words as $w)
			{
				$acronym .= $w[0];
			}
			$text = str_replace('$CI', $acronym, $text);
		}

		return $text;
	}

	private function _is_custom_invoice_number($cust_info)
	{
		$invoice_number = $this->config->config['sales_invoice_format'];
		$invoice_number = $this->_substitute_variables($invoice_number, $cust_info);

		return $this->sale_lib->get_invoice_number() != $invoice_number;
	}
	
	private function _substitute_variables($text, $cust_info)
	{
		$text = $this->_substitute_variable($text, '$YCO', $this->Sale, 'get_invoice_number_for_year');
		$text = $this->_substitute_variable($text, '$CO', $this->Sale , 'get_invoice_count');
		$text = $this->_substitute_variable($text, '$SCO', $this->Sale_suspended, 'get_invoice_count');
		$text = strftime($text);
		$text = $this->_substitute_customer($text, $cust_info);

		return $text;
	}
	
	private function _substitute_invoice_number($cust_info)
	{
		$invoice_number = $this->config->config['sales_invoice_format'];
		$invoice_number = $this->_substitute_variables($invoice_number, $cust_info);
		$this->sale_lib->set_invoice_number($invoice_number, TRUE);

		return $this->sale_lib->get_invoice_number();
	}

	private function _load_sale_data($sale_id)
	{
		$this->Sale->create_sales_items_temp_table();

		$this->sale_lib->clear_all();
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$this->sale_lib->copy_entire_sale($sale_id);
		$data['cart'] = $this->sale_lib->get_cart();
		$data['payments'] = $this->sale_lib->get_payments();
		$data['subtotal'] = $this->sale_lib->get_subtotal();
		$data['discounted_subtotal'] = $this->sale_lib->get_subtotal(TRUE);
		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);
		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['total'] = $this->sale_lib->get_total();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['receipt_title'] = $this->lang->line('sales_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($sale_info['sale_time']));
		$data['transaction_date'] = date($this->config->item('dateformat'), strtotime($sale_info['sale_time']));
		$data['show_stock_locations'] = $this->Stock_location->show_locations('sales');
		$data['amount_change'] = $this->sale_lib->get_amount_due() * -1;
		$data['amount_due'] = $this->sale_lib->get_amount_due();
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$emp_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $emp_info->first_name . ' ' . $emp_info->last_name;

		$customer_id = $this->sale_lib->get_customer();
		$cust_info = $this->_load_customer_data($customer_id, $data);
		
		$data['sale_id'] = 'POS ' . $sale_id;
		$data['comments'] = $sale_info['comment'];
		$data['invoice_number'] = $sale_info['invoice_number'];
		$data['company_info'] = implode("\n", array(
			$this->config->item('address'),
			$this->config->item('phone'),
			$this->config->item('account_number')
		));
		$data['barcode'] = $this->barcode_lib->generate_receipt_barcode($data['sale_id']);
		$data['print_after_sale'] = FALSE;

		return $data;
	}
	
	function receipt($sale_id)
	{
		$data = $this->_load_sale_data($sale_id);	
		$this->load->view("sales/receipt", $data);
		$this->sale_lib->clear_all();
	}
	
	function invoice($sale_id, $sale_info='')
	{
		if($sale_info == '')
		{
			$sale_info = $this->_load_sale_data($sale_id);
		}

		$this->load->view("sales/invoice", $sale_info);
		$this->sale_lib->clear_all();
	}
	
	function edit($sale_id)
	{
		$data = array();

		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee)
		{
			$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
		}
		$this->Sale->create_sales_items_temp_table();

		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$person_name = $sale_info['first_name'] . " " . $sale_info['last_name'];
		$data['selected_customer_name'] = !empty($sale_info['customer_id']) ? $person_name : '';
		$data['selected_customer_id'] = $sale_info['customer_id'];
		$data['sale_info'] = $sale_info;
		$data['payments'] = $this->Sale->get_sale_payments($sale_id);
		// don't allow gift card to be a payment option in a sale transaction edit because it's a complex change
		$data['payment_options'] = $this->Sale->get_payment_options(false);
		
		$this->load->view('sales/form', $data);
	}
	
	function delete($sale_id = -1, $update_inventory=TRUE)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$sale_ids = $sale_id == -1 ? $this->input->post('ids') : array($sale_id);

		if($this->Sale->delete_list($sale_ids, $employee_id, $update_inventory))
		{
			echo json_encode(array('success'=>true, 'message'=>$this->lang->line('sales_successfully_deleted').' '.
			count($sale_ids).' '.$this->lang->line('sales_one_or_multiple'), 'ids'=>$sale_ids));
		}
		else
		{
			echo json_encode(array('success'=>false, 'message'=>$this->lang->line('sales_unsuccessfully_deleted')));
		}
	}
	
	function save($sale_id)
	{
		$newdate = $this->input->post('date');
		
		$start_date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $newdate);

		$sale_data = array(
			'sale_time' => $start_date_formatter->format('Y-m-d H:i:s'),
			'customer_id' => $this->input->post('customer_id') != '' ? $this->input->post('customer_id') : null,
			'employee_id' => $this->input->post('employee_id'),
			'comment' => $this->input->post('comment'),
			'invoice_number' => $this->input->post('invoice_number') != '' ? $this->input->post('invoice_number') : null
		);
		
		// go through all the payment type input from the form, make sure the form matches the name and iterator number
		$payments = array();
		for($i = 0; $i < $this->input->post('number_of_payments'); $i++)
		{
			$payment_amount = $this->input->post('payment_amount_'.$i);
			$payment_type = $this->input->post('payment_type_'.$i);
			// remove any 0 payment if by mistake any was introduced at sale time
			if($payment_amount != 0)
			{
				// search for any payment of the same type that was already added, if that's the case add up the new payment amount
				$key = FALSE;
				if( !empty($payments) )
				{
					// search in the multi array the key of the entry containing the current payment_type (NOTE: in PHP5.5 the array_map could be replaced by an array_column)
					$key = array_search($payment_type, array_map(function($v){return $v['payment_type'];}, $payments));
				}

				// if no previous payment is found add a new one
				if( $key === FALSE )
				{
					$payments[] = array('payment_type'=>$payment_type, 'payment_amount'=>$payment_amount);
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
			echo json_encode(array('success'=>true, 'message'=>$this->lang->line('sales_successfully_updated'), 'id'=>$sale_id));
		}
		else
		{
			echo json_encode(array('success'=>false, 'message'=>$this->lang->line('sales_unsuccessfully_updated'), 'id'=>$sale_id));
		}
	}
	
	private function _payments_cover_total()
	{
		// Changed the conditional to account for floating point rounding
		
		// "sale" amount due needs to be <=0 to state it's fine
		if( ($this->sale_lib->get_mode() == 'sale') && $this->sale_lib->get_amount_due() > 1e-6 )
		{
			return false;
		}

		// "return" amount due needs to be >=0 to state it's fine		
		if( ($this->sale_lib->get_mode() == 'return') && $this->sale_lib->get_amount_due() < -(1e-6) )
		{
			return false;
		}
		
		return true;
	}
	
	private function _reload($data=array())
	{		
		$person_info = $this->Employee->get_logged_in_employee_info();
		$data['cart'] = $this->sale_lib->get_cart();	 
		$data['modes'] = array('sale'=>$this->lang->line('sales_sale'), 'return'=>$this->lang->line('sales_return'));
		$data['mode'] = $this->sale_lib->get_mode();

		$data['stock_locations'] = $this->Stock_location->get_allowed_locations('sales');
		$data['stock_location'] = $this->sale_lib->get_sale_location();
        
		$data['subtotal'] = $this->sale_lib->get_subtotal(TRUE);
		$data['tax_exclusive_subtotal'] = $this->sale_lib->get_subtotal(TRUE, TRUE);
		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['discount'] = $this->sale_lib->get_discount();
		$data['total'] = $this->sale_lib->get_total();
		$data['items_module_allowed'] = $this->Employee->has_grant('items', $person_info->person_id);
		$data['comment'] = $this->sale_lib->get_comment();
		$data['email_receipt'] = $this->sale_lib->get_email_receipt();
		$data['payments_total'] = $this->sale_lib->get_payments_total();
		$data['amount_due'] = $this->sale_lib->get_amount_due();
		$data['payments'] = $this->sale_lib->get_payments();
		$data['payment_options'] = $this->Sale->get_payment_options();

		$customer_id = $this->sale_lib->get_customer();
		$cust_info = $this->_load_customer_data($customer_id, $data, true);
		
		$data['invoice_number'] = $this->_substitute_invoice_number($cust_info);
		$data['invoice_number_enabled'] = $this->sale_lib->is_invoice_number_enabled();
		$data['print_after_sale'] = $this->sale_lib->is_print_after_sale();
		$data['payments_cover_total'] = $this->_payments_cover_total();

		$this->load->view("sales/register", $data);
	}
	
	private function _load_customer_data($customer_id, &$data, $totals=false)
	{	
		$cust_info = '';
		
		if($customer_id != -1)
		{
			$cust_info = $this->Customer->get_info($customer_id);
			if(isset($cust_info->company_name))
			{
				$data['customer'] = $cust_info->company_name;
			}
			else
			{
				$data['customer'] = $cust_info->first_name . ' ' . $cust_info->last_name;
			}
			$data['first_name'] = $cust_info->first_name;
			$data['last_name'] = $cust_info->last_name;
			$data['customer_email'] = $cust_info->email;
			$data['customer_address'] = $cust_info->address_1;
			if(!empty($cust_info->zip) or !empty($cust_info->city))
			{
				$data['customer_location'] = $cust_info->zip . ' ' . $cust_info->city;				
			}
			else
			{
				$data['customer_location'] = '';
			}
			$data['customer_account_number'] = $cust_info->account_number;
			$data['customer_discount_percent'] = $cust_info->discount_percent;
			if($totals)
			{
				$cust_totals = $this->Customer->get_totals($customer_id);

				$data['customer_total'] = $cust_totals->total;
			}
			$data['customer_info'] = implode("\n", array(
				$data['customer'],
				$data['customer_address'],
				$data['customer_location'],
				$data['customer_account_number']
			));
		}
		
		return $cust_info;
	}

	function cancel()
	{
		$this->sale_lib->clear_all();

		$this->_reload();
	}
	
	function suspend()
	{	
		$data['cart'] = $this->sale_lib->get_cart();
		$data['subtotal'] = $this->sale_lib->get_subtotal();
		$data['taxes'] = $this->sale_lib->get_taxes();
		$data['total'] = $this->sale_lib->get_total();
		$data['receipt_title'] = $this->lang->line('sales_receipt');
		$data['transaction_time'] = date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'));
		$comment = $this->sale_lib->get_comment();
		$invoice_number = $this->sale_lib->get_invoice_number();

		$data['payment_type'] = $this->input->post('payment_type');
		// Multiple payments
		$data['payments'] = $this->sale_lib->get_payments();
		$data['amount_change'] = to_currency($this->sale_lib->get_amount_due() * -1);

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$emp_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $emp_info->first_name . ' ' . $emp_info->last_name;

		$customer_id = $this->sale_lib->get_customer();
		$cust_info = $this->_load_customer_data($customer_id, $data);

		$is_set = $this->_is_custom_invoice_number($cust_info);
		$invoice_number = $is_set ? $invoice_number : NULL;

		$total_payments = 0;

		foreach($data['payments'] as $payment)
		{
			$total_payments = bcadd($total_payments, $payment['payment_amount'], PRECISION);
		}

		//SAVE sale to database
		$data['sale_id'] = 'POS ' . $this->Sale_suspended->save($data['cart'], $customer_id, $employee_id, $comment, $invoice_number, $data['payments']);
		if ($data['sale_id'] == 'POS -1')
		{
			$data['error_message'] = $this->lang->line('sales_transaction_failed');
		}

		$this->sale_lib->clear_all();

		$this->_reload(array('success' => $this->lang->line('sales_successfully_suspended_sale')));
	}
	
	function suspended()
	{	
		$data = array();
		$data['suspended_sales'] = $this->Sale_suspended->get_all()->result_array();

		$this->load->view('sales/suspended', $data);
	}
	
	function unsuspend()
	{
		$sale_id = $this->input->post('suspended_sale_id');
		$this->sale_lib->clear_all();
		$this->sale_lib->copy_entire_suspended_sale($sale_id);
		$this->Sale_suspended->delete($sale_id);

		$this->_reload();
	}
	
	function check_invoice_number()
	{
		$sale_id = $this->input->post('sale_id');
		$invoice_number = $this->input->post('invoice_number');
		$exists = !empty($invoice_number) && $this->Sale->invoice_number_exists($invoice_number,$sale_id);
		echo !$exists ? 'true' : 'false';
	}
}
?>
