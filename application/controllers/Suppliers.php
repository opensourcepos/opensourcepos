<?php
require_once ("Person_controller.php");
class Suppliers extends Person_controller
{
	function __construct()
	{
		parent::__construct('suppliers');
	}
	
	function index($limit_from=0)
	{
		$data['controller_name'] = $this->get_controller_name();
		$lines_per_page = $this->Appconfig->get('lines_per_page');
		$suppliers = $this->Supplier->get_all($lines_per_page);
		
		$data['links'] = $this->_initialize_pagination($this->Supplier, $lines_per_page, $limit_from);
		$data['manage_table'] = get_supplier_manage_table($suppliers, $this);
		$this->load->view('suppliers/manage', $data);
	}
	
	/*
	Returns Supplier table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search = $this->input->post('search') != '' ? $this->input->post('search') : null;
		$limit_from = $this->input->post('limit_from');
		$lines_per_page = $this->Appconfig->get('lines_per_page');

		$suppliers = $this->Supplier->search($search, $lines_per_page, $limit_from);
		$total_rows = $this->Supplier->get_found_rows($search);
		$links = $this->_initialize_pagination($this->Supplier, $lines_per_page, $limit_from, $total_rows);
		$data_rows = get_supplier_manage_table_data_rows($suppliers, $this);

		echo json_encode(array('total_rows' => $total_rows, 'rows' => $data_rows, 'pagination' => $links));
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Supplier->get_search_suggestions($this->input->get('term'), TRUE);
		echo json_encode($suggestions);
	}

	function suggest_search()
	{
		$suggestions = $this->Supplier->get_search_suggestions($this->input->post('term'), FALSE);
		echo json_encode($suggestions);
	}
	
	/*
	Loads the supplier edit form
	*/
	function view($supplier_id=-1)
	{
		$data['person_info']=$this->Supplier->get_info($supplier_id);
		$this->load->view("suppliers/form",$data);
	}
	
	/*
	Inserts/updates a supplier
	*/
	function save($supplier_id=-1)
	{
		$person_data = array(
			'first_name'=>$this->input->post('first_name'),
			'last_name'=>$this->input->post('last_name'),
			'gender'=>$this->input->post('gender'),
			'email'=>$this->input->post('email'),
			'phone_number'=>$this->input->post('phone_number'),
			'address_1'=>$this->input->post('address_1'),
			'address_2'=>$this->input->post('address_2'),
			'city'=>$this->input->post('city'),
			'state'=>$this->input->post('state'),
			'zip'=>$this->input->post('zip'),
			'country'=>$this->input->post('country'),
			'comments'=>$this->input->post('comments')
		);
		$supplier_data=array(
			'company_name'=>$this->input->post('company_name'),
			'agency_name'=>$this->input->post('agency_name'),
			'account_number'=>$this->input->post('account_number') == '' ? null : $this->input->post('account_number')
		);
		if($this->Supplier->save_supplier($person_data,$supplier_data,$supplier_id))
		{
			//New supplier
			if($supplier_id==-1)
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('suppliers_successful_adding').' '.
				$supplier_data['company_name'],'person_id'=>$supplier_data['person_id']));
			}
			else //previous supplier
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('suppliers_successful_updating').' '.
				$supplier_data['company_name'],'person_id'=>$supplier_id));
			}
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('suppliers_error_adding_updating').' '.
			$supplier_data['company_name'],'person_id'=>-1));
		}
	}
	
	/*
	This deletes suppliers from the suppliers table
	*/
	function delete()
	{
		$suppliers_to_delete=$this->input->post('ids');
		
		if($this->Supplier->delete_list($suppliers_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('suppliers_successful_deleted').' '.
			count($suppliers_to_delete).' '.$this->lang->line('suppliers_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('suppliers_cannot_be_deleted')));
		}
	}
	
	/*
	Gets one row for a supplier manage table. This is called using AJAX to update one row.
	*/
	function get_row()
	{
		$person_id = $this->input->post('row_id');
		$data_row=get_supplier_data_row($this->Supplier->get_info($person_id),$this);
		echo $data_row;
	}
}
?>