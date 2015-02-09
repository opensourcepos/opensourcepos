<?php
require_once ("secure_area.php");
class Sales extends Secure_area
{
	function __construct()
	{
		parent::__construct('sales');
		$this->load->library('sale_lib');       
	}

	function index()
	{
		$this->_reload();
	}

	function item_search()
	{
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions($this->input->post('q'),$this->input->post('limit')));
		echo implode("\n",$suggestions);
	}

	function customer_search()
	{
		$suggestions = $this->Customer->get_customer_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function select_customer()
	{
		$customer_id = $this->input->post("customer");
		$this->sale_lib->set_customer($customer_id);
		$this->_reload();
	}

	function change_mode()
	{
		$stock_location = $this->input->post("stock_location");
		if (!$stock_location || $stock_location == $this->sale_lib->get_sale_location()) 
		{
			$this->sale_lib->clear_all();
			$mode = $this->input->post("mode");
			$this->sale_lib->set_mode($mode);
		} 
		else
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
	
	function set_email_receipt()
	{
 	  $this->sale_lib->set_email_receipt($this->input->post('email_receipt'));
	}

	//Alain Multiple Payments
	function add_payment()
	{		
		$data = array();
		$this->form_validation->set_rules( 'amount_tendered', 'lang:sales_amount_tendered', 'numeric' );
		
		if ( $this->form_validation->run() == FALSE )
		{
			if ( $this->input->post( 'payment_type' ) == $this->lang->line( 'sales_gift_card' ) )
				$data['error']=$this->lang->line('sales_must_enter_numeric_giftcard');
			else
				$data['error']=$this->lang->line('sales_must_enter_numeric');
				
 			$this->_reload( $data );
 			return;
		}
		
		$payment_type = $this->input->post( 'payment_type' );
		if ( $payment_type == $this->lang->line( 'sales_giftcard' ) )
		{
			$payments = $this->sale_lib->get_payments();
			$payment_type = $this->input->post( 'payment_type' ) . ':' . $payment_amount = $this->input->post( 'amount_tendered' );
			$current_payments_with_giftcard = isset( $payments[$payment_type] ) ? $payments[$payment_type]['payment_amount'] : 0;
			$cur_giftcard_value = $this->Giftcard->get_giftcard_value( $this->input->post( 'amount_tendered' ) ) - $current_payments_with_giftcard;
			
			if ( $cur_giftcard_value <= 0 )
			{
				$data['error'] = 'Giftcard balance is ' . to_currency( $this->Giftcard->get_giftcard_value( $this->input->post( 'amount_tendered' ) ) ) . ' !';
				$this->_reload( $data );
				return;
			}

			$new_giftcard_value = $this->Giftcard->get_giftcard_value( $this->input->post( 'amount_tendered' ) ) - $this->sale_lib->get_amount_due( );
			$new_giftcard_value = ( $new_giftcard_value >= 0 ) ? $new_giftcard_value : 0;
			$data['warning'] = 'Giftcard ' . $this->input->post( 'amount_tendered' ) . ' balance is ' . to_currency( $new_giftcard_value ) . ' !';
			$payment_amount = min( $this->sale_lib->get_amount_due( ), $this->Giftcard->get_giftcard_value( $this->input->post( 'amount_tendered' ) ) );
		}
		else
		{
			$payment_amount = $this->input->post( 'amount_tendered' );
		}
		
		if( !$this->sale_lib->add_payment( $payment_type, $payment_amount ) )
		{
			$data['error']='Unable to Add Payment! Please try again!';
		}
		
		$this->_reload($data);
	}

	//Alain Multiple Payments
	function delete_payment( $payment_id )
	{
		$this->sale_lib->delete_payment( $payment_id );
		$this->_reload();
	}

	function add()
	{
		$data=array();
		$mode = $this->sale_lib->get_mode();
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post("item");
		$quantity = ($mode=="return")? -1:1;
		$item_location = $this->sale_lib->get_sale_location();

		if($mode == 'return' && $this->sale_lib->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->sale_lib->return_entire_sale($item_id_or_number_or_item_kit_or_receipt);
		}
		elseif($this->Sale_suspended->invoice_number_exists($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->sale_lib->clear_all();
			$sale_id=$this->Sale_suspended->get_sale_by_invoice_number($item_id_or_number_or_item_kit_or_receipt)->row()->sale_id;
			$this->sale_lib->copy_entire_suspended_sale($sale_id);
			$this->Sale_suspended->delete($sale_id);
		}
		elseif($this->sale_lib->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->sale_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt,$item_location);
		}
		elseif(!$this->sale_lib->add_item($item_id_or_number_or_item_kit_or_receipt,$quantity,$item_location))
		{
			$data['error']=$this->lang->line('sales_unable_to_add_item');
		}
		
		if($this->sale_lib->out_of_stock($item_id_or_number_or_item_kit_or_receipt,$item_location))
		{
			$data['warning'] = $this->lang->line('sales_quantity_less_than_zero');
		}
		$this->_reload($data);
	}

	function edit_item($line)
	{
		$data= array();

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|numeric');
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|numeric');

        $description = $this->input->post("description");
        $serialnumber = $this->input->post("serialnumber");
		$price = $this->input->post("price");
		$quantity = $this->input->post("quantity");
		$discount = $this->input->post("discount");
		$item_location = $this->input->post("location");


		if ($this->form_validation->run() != FALSE)
		{
			$this->sale_lib->edit_item($line,$description,$serialnumber,$quantity,$discount,$price);
		}
		else
		{
			$data['error']=$this->lang->line('sales_error_editing_item');
		}
		
		if($this->sale_lib->out_of_stock($this->sale_lib->get_item_id($line),$item_location))
		{
			$data['warning'] = $this->lang->line('sales_quantity_less_than_zero');
		}


		$this->_reload($data);
	}

	function delete_item($item_number)
	{
		$this->sale_lib->delete_item($item_number);
		$this->_reload();
	}

	function remove_customer()
	{
		$this->sale_lib->clear_invoice_number();
		$this->sale_lib->remove_customer();
		$this->_reload();
	}

	function complete()
	{
		$data['cart']=$this->sale_lib->get_cart();
		$data['subtotal']=$this->sale_lib->get_subtotal();
		$data['taxes']=$this->sale_lib->get_taxes();
		$data['total']=$this->sale_lib->get_total();
		$data['receipt_title']=$this->lang->line('sales_receipt');
		$data['transaction_time']= date('m/d/Y h:i:s a');
		$stock_locations=$this->Stock_locations->get_undeleted_all()->result_array();
		$data['show_stock_locations']=count($stock_locations) > 1;
		$customer_id=$this->sale_lib->get_customer();
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$comment=$this->sale_lib->get_comment();
		$emp_info=$this->Employee->get_info($employee_id);
		$data['payments']=$this->sale_lib->get_payments();
		$data['amount_change']=to_currency($this->sale_lib->get_amount_due() * -1);
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
        $cust_info='';
		if($customer_id!=-1)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name;
		}
		$invoice_number=$this->_substitute_invoice_number($cust_info);
		if ($this->sale_lib->is_invoice_number_enabled() && $this->Sale->invoice_number_exists($invoice_number))
		{
			$data['error']=$this->lang->line('sales_invoice_number_duplicate');
			$this->_reload($data);
		}
		else 
		{
			$invoice_number = $this->sale_lib->is_invoice_number_enabled() ? $invoice_number : NULL;
			$data['invoice_number']=$invoice_number;
			$data['sale_id']='POS '.$this->Sale->save($data['cart'], $customer_id,$employee_id,$comment,$invoice_number,$data['payments']);
			if ($data['sale_id'] == 'POS -1')
			{
				$data['error_message'] = $this->lang->line('sales_transaction_failed');
			}
			else
			{
				if ($this->sale_lib->get_email_receipt() && !empty($cust_info->email))
				{
					$this->load->library('email');
					$config['mailtype'] = 'html';				
					$this->email->initialize($config);
					$this->email->from($this->config->item('email'), $this->config->item('company'));
					$this->email->to($cust_info->email); 
	
					$this->email->subject($this->lang->line('sales_receipt'));
					$this->email->message($this->load->view("sales/receipt_email",$data, true));	
					$this->email->send();
				}
			}
			$this->load->view("sales/receipt",$data);
			$this->sale_lib->clear_all();
		}

		$this->_remove_duplicate_cookies();
	}
	
	function _substitute_invoice_number($customer_info='')
	{
		$invoice_number=$this->sale_lib->get_invoice_number();
		if (empty($invoice_number))
		{
			$invoice_number=$this->config->config['sales_invoice_format'];
		}
		$invoice_count=$this->Sale->get_invoice_count();
		$invoice_number=str_replace('$CO',$invoice_count,$invoice_number);
		$invoice_count=$this->Sale_suspended->get_invoice_count();
		$invoice_number=str_replace('$SCO',$invoice_count,$invoice_number);
		$invoice_number=strftime($invoice_number);
	
		$customer_id=$this->sale_lib->get_customer();
		if($customer_id!=-1)
		{
			$invoice_number=str_replace('$CU',$customer_info->first_name . ' ' . $customer_info->last_name,$invoice_number);
			$words = preg_split("/\s+/", $customer_info->first_name . ' ' . $customer_info->last_name);
			$acronym = "";
			foreach ($words as $w) {
				$acronym .= $w[0];
			}
			$invoice_number=str_replace('$CI',$acronym,$invoice_number);
		}
		$this->sale_lib->set_invoice_number($invoice_number);
		return $invoice_number;
	}
	
	function receipt($sale_id)
	{
		$this->load->library('barcode_lib');
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$this->sale_lib->copy_entire_sale($sale_id);
		$stock_locations = $this->Stock_locations->get_undeleted_all()->result_array();
		$data['show_stock_locations'] = count($stock_locations) > 1;
		$data['cart']=$this->sale_lib->get_cart();
		$data['payments']=$this->sale_lib->get_payments();
		$data['subtotal']=$this->sale_lib->get_subtotal();
		$data['taxes']=$this->sale_lib->get_taxes();
		$data['total']=$this->sale_lib->get_total();
		$data['receipt_title']=$this->lang->line('sales_receipt');
		$data['transaction_time']= date('m/d/Y h:i:s a', strtotime($sale_info['sale_time']));
		$customer_id=$this->sale_lib->get_customer();
		$emp_info=$this->Employee->get_info($sale_info['employee_id']);
		$data['payment_type']=$sale_info['payment_type'];
		$data['invoice_number']=$sale_info['invoice_number'];
		$data['amount_change']=to_currency($this->sale_lib->get_amount_due() * -1);
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;

		if($customer_id!=-1)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name;
		}
		$data['sale_id']='POS '.$sale_id;
		$barcode_config=array('barcode_type'=>1,'barcode_width'=>180, 'barcode_height'=>30, 'barcode_quality'=>100);
		$data['barcode']=$this->barcode_lib->generate_barcode($sale_id,$barcode_config);
		$this->load->view("sales/receipt",$data);
		$this->sale_lib->clear_all();
		$this->_remove_duplicate_cookies();
	}
	
	function edit($sale_id)
	{
		$data = array();

		$data['customers'] = array('' => 'No Customer');
		foreach ($this->Customer->get_all()->result() as $customer)
		{
			$data['customers'][$customer->person_id] = $customer->first_name . ' '. $customer->last_name;
		}

		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee)
		{
			$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
		}

		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$person_name = $sale_info['first_name'] . " " . $sale_info['last_name'];
		$data['selected_customer'] = !empty($sale_info['customer_id']) ? $sale_info['customer_id'] . "|" . $person_name : "";
		$data['sale_info'] = $sale_info;
		
		$this->load->view('sales/form', $data);
	}
	
	function delete($sale_id = -1, $update_inventory=TRUE) {
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$sale_ids= $sale_id == -1 ? $this->input->post('ids') : array($sale_id);

		if($this->Sale->delete_list($sale_ids, $employee_id, $update_inventory))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('sales_successfully_deleted').' '.
			count($sale_ids).' '.$this->lang->line('sales_one_or_multiple'),'ids'=>$sale_ids));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('sales_unsuccessfully_deleted')));
		}
	}
	
	function save($sale_id)
	{
		$sale_data = array(
			'sale_time' => date('Y-m-d', strtotime($this->input->post('date'))),
			'customer_id' => $this->input->post('customer_id') ? $this->input->post('customer_id') : NULL,
			'employee_id' => $this->input->post('employee_id'),
			'comment' => $this->input->post('comment'),
			'invoice_number' => $this->input->post('invoice_number') ? $this->input->post('invoice_number') : NULL
		);
		
		if ($this->Sale->update($sale_data, $sale_id))
		{
			echo json_encode(array(
				'success'=>true,
				'message'=>$this->lang->line('sales_successfully_updated'),
				'id'=>$sale_id)
			);
		}
		else
		{
			echo json_encode(array(
				'success'=>false,
				'message'=>$this->lang->line('sales_unsuccessfully_updated'),
				'id'=>$sale_id)
			);
		}
	}
	
	function _payments_cover_total()
	{
		$total_payments = 0;

		foreach($this->sale_lib->get_payments() as $payment)
		{
			$total_payments += $payment['payment_amount'];
		}

		/* Changed the conditional to account for floating point rounding */
		if ( ($this->sale_lib->get_mode() == 'sale') && 
		      ( ( to_currency_no_money( $this->sale_lib->get_total() ) - $total_payments ) > 1e-6 ) )
		{
			return false;
		}
		
		return true;
	}
	
	function _reload($data=array())
	{
		$person_info = $this->Employee->get_logged_in_employee_info();
		$data['cart']=$this->sale_lib->get_cart();	 
        $data['modes']=array('sale'=>$this->lang->line('sales_sale'),'return'=>$this->lang->line('sales_return'));
        $data['mode']=$this->sale_lib->get_mode();
                     
        $data['stock_locations']=$this->Stock_locations->get_allowed_locations();
        $data['stock_location']=$this->sale_lib->get_sale_location();
        
		$data['subtotal']=$this->sale_lib->get_subtotal();
		$data['taxes']=$this->sale_lib->get_taxes();
		$data['total']=$this->sale_lib->get_total();
		$data['items_module_allowed']=$this->Employee->has_grant('items', $person_info->person_id);
		$data['comment']=$this->sale_lib->get_comment();
		$data['email_receipt']=$this->sale_lib->get_email_receipt();
		$data['payments_total']=$this->sale_lib->get_payments_total();
		$data['amount_due']=$this->sale_lib->get_amount_due();
		$data['payments']=$this->sale_lib->get_payments();
		$data['payment_options']=array(
			$this->lang->line('sales_cash') => $this->lang->line('sales_cash'),
			$this->lang->line('sales_check') => $this->lang->line('sales_check'),
			$this->lang->line('sales_giftcard') => $this->lang->line('sales_giftcard'),
			$this->lang->line('sales_debit') => $this->lang->line('sales_debit'),
			$this->lang->line('sales_credit') => $this->lang->line('sales_credit')
		);

		$customer_id=$this->sale_lib->get_customer();
		$cust_info='';
		if($customer_id!=-1)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name;
			$data['customer_email']=$cust_info->email;
		}
		$data['invoice_number']=$this->_substitute_invoice_number($cust_info);
		$data['invoice_number_enabled']=$this->sale_lib->is_invoice_number_enabled();
		$data['payments_cover_total']=$this->_payments_cover_total();
		$this->load->view("sales/register",$data);
		$this->_remove_duplicate_cookies();
	}

    function cancel_sale()
    {
    	$this->sale_lib->clear_all();
    	$this->_reload();
    }
	
	function suspend()
	{
		$data['cart']=$this->sale_lib->get_cart();
		$data['subtotal']=$this->sale_lib->get_subtotal();
		$data['taxes']=$this->sale_lib->get_taxes();
		$data['total']=$this->sale_lib->get_total();
		$data['receipt_title']=$this->lang->line('sales_receipt');
		$data['transaction_time']= date('m/d/Y h:i:s a');
		$customer_id=$this->sale_lib->get_customer();
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$comment = $this->input->post('comment');
		$invoice_number=$this->sale_lib->get_invoice_number();
		
		$emp_info=$this->Employee->get_info($employee_id);
		$payment_type = $this->input->post('payment_type');
		$data['payment_type']=$this->input->post('payment_type');
		//Alain Multiple payments
		$data['payments']=$this->sale_lib->get_payments();
		$data['amount_change']=to_currency($this->sale_lib->get_amount_due() * -1);
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
		
		if ($this->Sale_suspended->invoice_number_exists($invoice_number))
		{
			$this->_reload(array('error' => $data['error']=$this->lang->line('sales_invoice_number_duplicate')));
		}
		else
		{
			if($customer_id!=-1)
			{
				$cust_info=$this->Customer->get_info($customer_id);
				$data['customer']=$cust_info->first_name.' '.$cust_info->last_name;
			}
	
			$total_payments = 0;
	
			foreach($data['payments'] as $payment)
			{
				$total_payments += $payment['payment_amount'];
			}
	
			//SAVE sale to database
			$data['sale_id']='POS '.$this->Sale_suspended->save($data['cart'], $customer_id,$employee_id,$comment,$invoice_number,$data['payments']);
			if ($data['sale_id'] == 'POS -1')
			{
				$data['error_message'] = $this->lang->line('sales_transaction_failed');
			}
			$this->sale_lib->clear_all();
			$this->_reload(array('success' => $this->lang->line('sales_successfully_suspended_sale')));
		}
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
		$sale_id=$this->input->post('sale_id');
		$invoice_number=$this->input->post('invoice_number');
		$exists=!empty($invoice_number) && $this->Sale->invoice_number_exists($invoice_number,$sale_id);
		echo json_encode(array('success'=>!$exists,'message'=>$this->lang->line('sales_invoice_number_duplicate')));
	}
}
?>
