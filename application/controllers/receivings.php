<?php
require_once ("secure_area.php");
class Receivings extends Secure_area
{
	function __construct()
	{
		parent::__construct('receivings');
		$this->load->library('receiving_lib');
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

	function supplier_search()
	{
		$suggestions = $this->Supplier->get_suppliers_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function select_supplier()
	{
		$supplier_id = $this->input->post("supplier");
		$this->receiving_lib->set_supplier($supplier_id);
		$this->_reload();
	}

	function change_mode()
	{
		$mode = $this->input->post("mode");
		$this->receiving_lib->set_mode($mode);
        
        $stock_source = $this->input->post("stock_source");
        $this->receiving_lib->set_stock_source($stock_source);
        
        $stock_deatination = $this->input->post("stock_deatination");
        $this->receiving_lib->set_stock_destination($stock_deatination);
        $this->receiving_lib->empty_cart();        
		$this->_reload();
	}

	function add()
	{
		$data=array();
		$mode = $this->receiving_lib->get_mode();
		$item_id_or_number_or_item_kit_or_receipt = $this->input->post("item");
		$quantity = ($mode=="receive" or $mode=="requisition") ? 1:-1;

		if($this->receiving_lib->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt) && $mode=='return')
		{
			$this->receiving_lib->return_entire_receiving($item_id_or_number_or_item_kit_or_receipt);
		}
		elseif($this->receiving_lib->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->receiving_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt);
		}
		else
		{
		    if($mode == 'requisition')
            {
                if(!$this->receiving_lib->add_item_unit($item_id_or_number_or_item_kit_or_receipt))
                     $data['error']=$this->lang->line('reqs_unable_to_add_item');
            }
            else
            {
                if(!$this->receiving_lib->add_item($item_id_or_number_or_item_kit_or_receipt,$quantity))
                    $data['error']=$this->lang->line('recvs_unable_to_add_item');
            }
		   
		}
		$this->_reload($data);
	}

	function edit_item($item_id)
	{
		$data= array();

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|numeric');
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|integer');
		$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|integer');

    	$description = $this->input->post("description");
    	$serialnumber = $this->input->post("serialnumber");
		$price = $this->input->post("price");
		$quantity = $this->input->post("quantity");
		$discount = $this->input->post("discount");

		if ($this->form_validation->run() != FALSE)
		{
			$this->receiving_lib->edit_item($item_id,$description,$serialnumber,$quantity,$discount,$price);
		}
		else
		{
			$data['error']=$this->lang->line('recvs_error_editing_item');
		}

		$this->_reload($data);
	}

    function edit_item_unit($item_id)
    {
        $data= array();

        $this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|integer'); 
        $quantity = $this->input->post("quantity");
        

        if ($this->form_validation->run() != FALSE)
        {
            $this->receiving_lib->edit_item_unit($item_id,$description,$quantity,0,0);
        }
        else
        {
            $data['error']=$this->lang->line('recvs_error_editing_item');
        }

        $this->_reload($data);
    }

	function delete_item($item_number)
	{
		$this->receiving_lib->delete_item($item_number);
		$this->_reload();
	}

	function delete_supplier()
	{
		$this->receiving_lib->delete_supplier();
		$this->_reload();
	}

	function complete()
	{
		$data['cart']=$this->receiving_lib->get_cart();
		$data['total']=$this->receiving_lib->get_total();
		$data['receipt_title']=$this->lang->line('recvs_receipt');
		$data['transaction_time']= date('m/d/Y h:i:s a');
		$supplier_id=$this->receiving_lib->get_supplier();
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$comment = $this->input->post('comment');
		$emp_info=$this->Employee->get_info($employee_id);
		$payment_type = $this->input->post('payment_type');
		$data['payment_type']=$this->input->post('payment_type');
        $data['stock_location']=$this->receiving_lib->get_stock_source();

		if ($this->input->post('amount_tendered'))
		{
			$data['amount_tendered'] = $this->input->post('amount_tendered');
			$data['amount_change'] = to_currency($data['amount_tendered'] - round($data['total'], 2));
		}
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;

		if($supplier_id!=-1)
		{
			$suppl_info=$this->Supplier->get_info($supplier_id);
			$data['supplier']=$suppl_info->first_name.' '.$suppl_info->last_name;
		}

		//SAVE receiving to database
		$data['receiving_id']='RECV '.$this->Receiving->save($data['cart'], $supplier_id,$employee_id,$comment,$payment_type,$data['stock_location']);
		
		if ($data['receiving_id'] == 'RECV -1')
		{
			$data['error_message'] = $this->lang->line('receivings_transaction_failed');
		}

		$this->load->view("receivings/receipt",$data);
		$this->receiving_lib->clear_all();
	}

    function requisition_complete()
    {
        $data['cart']=$this->receiving_lib->get_cart();
        $data['receipt_title']=$this->lang->line('reqs_receipt');
        $data['transaction_time']= date('m/d/Y h:i:s a');

        $employee_id=$this->Employee->get_logged_in_employee_info()->person_id;   
        $emp_info=$this->Employee->get_info($employee_id);
        $data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
         
        $comment = $this->input->post('comment');
        
        //SAVE requisition to database
        $data['requisition_id']='REQS '.$this->Receiving->save_requisition($data['cart'],$employee_id,$comment);
        
        if ($data['requisition_id'] == 'REQS -1')
        {
            $data['error_message'] = $this->lang->line('reqs_transaction_failed');
        }

        $this->load->view("receivings/requisition_receipt",$data);
        $this->receiving_lib->clear_all();
    }
    
    function requisition_receipt($requisition_id)
    {
    	$requisition_info = $this->Receiving->get_requisition_info($requisition_id)->row_array();
    	$this->receiving_lib->copy_entire_requisition($requisition_id);

    	$data['cart']=$this->receiving_lib->get_cart();
    	$data['receipt_title']=$this->lang->line('reqs_receipt');
    	$data['transaction_time']= date('m/d/Y h:i:s a');
    	
    	$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
    	$emp_info=$this->Employee->get_info($employee_id);
    	$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
    	
    	$data['requisition_id']='REQS '.$requisition_id;
    	$this->load->view("receivings/requisition_receipt",$data);
    	$this->receiving_lib->clear_all();
    }

	function receipt($receiving_id)
	{
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();
		$this->receiving_lib->copy_entire_receiving($receiving_id);
		$data['cart']=$this->receiving_lib->get_cart();
		$data['total']=$this->receiving_lib->get_total();
		$data['receipt_title']=$this->lang->line('recvs_receipt');
		$data['transaction_time']= date('m/d/Y h:i:s a', strtotime($receiving_info['receiving_time']));
		$supplier_id=$this->receiving_lib->get_supplier();
		$emp_info=$this->Employee->get_info($receiving_info['employee_id']);
		$data['payment_type']=$receiving_info['payment_type'];

		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;

		if($supplier_id!=-1)
		{
			$supplier_info=$this->Supplier->get_info($supplier_id);
			$data['supplier']=$supplier_info->first_name.' '.$supplier_info->last_name;
		}
		$data['receiving_id']='RECV '.$receiving_id;
		$this->load->view("receivings/receipt",$data);
		$this->receiving_lib->clear_all();

	}

	function _reload($data=array())
	{
		$person_info = $this->Employee->get_logged_in_employee_info();
		$data['cart']=$this->receiving_lib->get_cart();
		$data['modes']=array('receive'=>$this->lang->line('recvs_receiving'),'return'=>$this->lang->line('recvs_return'), 'requisition'=>$this->lang->line('recvs_requisition'));
		$data['mode']=$this->receiving_lib->get_mode();
        
        $data['stock_locations'] = array();
        $stock_locations = $this->Stock_locations->get_undeleted_all()->result_array();          
        foreach($stock_locations as $location_data)
        {            
            $data['stock_locations']['stock_'.$location_data['location_id']] = $location_data['location_name'];
        }     
        
        $data['stock_source']=$this->receiving_lib->get_stock_source();
        $data['stock_destination']=$this->receiving_lib->get_stock_destination();
        
		$data['total']=$this->receiving_lib->get_total();
		$data['items_module_allowed'] = $this->Employee->has_permission('items', $person_info->person_id);
		$data['payment_options']=array(
			$this->lang->line('sales_cash') => $this->lang->line('sales_cash'),
			$this->lang->line('sales_check') => $this->lang->line('sales_check'),
			$this->lang->line('sales_debit') => $this->lang->line('sales_debit'),
			$this->lang->line('sales_credit') => $this->lang->line('sales_credit')
		);

		$supplier_id=$this->receiving_lib->get_supplier();
		if($supplier_id!=-1)
		{
			$info=$this->Supplier->get_info($supplier_id);
			$data['supplier']=$info->first_name.' '.$info->last_name;
		}
		$this->load->view("receivings/receiving",$data);
	}

    function cancel_receiving()
    {
    	$this->receiving_lib->clear_all();
    	$this->_reload();
    }

}
?>