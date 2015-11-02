<?php
require_once ("Person_controller.php");
class Employees extends Person_controller
{
	function __construct()
	{
		parent::__construct('employees');
	}
	
	function index($limit_from=0)
	{
		$data['controller_name']=$this->get_controller_name();
		$data['form_width']=$this->get_form_width();
		$lines_per_page = $this->Appconfig->get('lines_per_page');
		$suppliers = $this->Employee->get_all($lines_per_page,$limit_from);
		$data['links'] = $this->_initialize_pagination($this->Employee,$lines_per_page,$limit_from);
		$data['manage_table']=get_people_manage_table($suppliers,$this);
		$this->load->view('suppliers/manage',$data);
	}
	
	/*
	Returns employee table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search = $this->input->post('search');
		$limit_from = $this->input->post('limit_from');
		$lines_per_page = $this->Appconfig->get('lines_per_page');
		$employees = $this->Employee->search($search, $limit_from, $lines_per_page);
		$total_rows = $this->Employee->get_found_rows($search);
		$links = $this->_initialize_pagination($this->Employee, $lines_per_page, $limit_from, $total_rows);
		$data_rows=get_people_manage_table_data_rows($employees,$this);
		echo json_encode(array('rows' => $data_rows, 'pagination' => $links));
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Employee->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
	
	/*
	Loads the employee edit form
	*/
	function view($employee_id=-1)
	{
		$data['person_info']=$this->Employee->get_info($employee_id);
		$data['all_modules']=$this->Module->get_all_modules();
		$data['all_subpermissions']=$this->Module->get_all_subpermissions();
		$this->load->view("employees/form",$data);
	}
	
	/*
	Inserts/updates an employee
	*/
	function save($employee_id=-1)
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
		$grants_data = $this->input->post("grants")!=FALSE ? $this->input->post("grants"):array();
		
		//Password has been changed OR first time password set
		if($this->input->post('password')!='')
		{
			$employee_data=array(
			'username'=>$this->input->post('username'),
			'password'=>md5($this->input->post('password'))
			);
		}
		else //Password not changed
		{
			$employee_data=array('username'=>$this->input->post('username'));
		}
		
		if($this->Employee->save_employee($person_data,$employee_data,$grants_data,$employee_id))
		{
			//New employee
			if($employee_id==-1)
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('employees_successful_adding').' '.
				$person_data['first_name'].' '.$person_data['last_name'],'person_id'=>$employee_data['person_id']));
			}
			else //previous employee
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('employees_successful_updating').' '.
				$person_data['first_name'].' '.$person_data['last_name'],'person_id'=>$employee_id));
			}
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('employees_error_adding_updating').' '.
			$person_data['first_name'].' '.$person_data['last_name'],'person_id'=>-1));
		}
	}
	
	/*
	This deletes employees from the employees table
	*/
	function delete()
	{
		$employees_to_delete=$this->input->post('ids');
		
		if($this->Employee->delete_list($employees_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('employees_successful_deleted').' '.
			count($employees_to_delete).' '.$this->lang->line('employees_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('employees_cannot_be_deleted')));
		}
	}
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{
		return 750;
	}
}
?>