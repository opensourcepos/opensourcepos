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
		$data['table_headers'] = get_suppliers_manage_table_headers();

		$this->load->view('people/manage', $data);
	}
	
	/*
	Returns Supplier table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$lines_per_page = $this->Appconfig->get('lines_per_page');

		$suppliers = $this->Supplier->search($search, $offset, $limit);
		$total_rows = $this->Supplier->get_found_rows($search);
		$links = $this->_initialize_pagination($this->Employee, $lines_per_page, $limit, $total_rows);
		$data_rows = array();
		foreach($suppliers->result() as $supplier)
		{
			$data_rows[] = get_supplier_data_row($supplier, $this);
		}
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
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
				$supplier_data['company_name'],'id'=>$supplier_data['person_id']));
			}
			else //previous supplier
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('suppliers_successful_updating').' '.
				$supplier_data['company_name'],'id'=>$supplier_id));
			}
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('suppliers_error_adding_updating').' '.
			$supplier_data['company_name'],'id'=>-1));
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
	
}
?>