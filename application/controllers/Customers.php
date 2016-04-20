<?php
require_once ("Person_controller.php");
class Customers extends Person_controller
{
	function __construct()
	{
		parent::__construct('customers');
	}
	
	function index()
	{
		$data['controller_name'] = $this->get_controller_name();
		$data['table_headers'] = get_people_manage_table_headers();

		$this->load->view('people/manage', $data);
	}
	
	/*
	Returns customer table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search = $this->input->get('search');
		$limit = $this->input->get('limit');
		$offset = $this->input->get('offset');

		$customers = $this->Customer->search($search, $limit, $offset);
		$total_rows = $this->Customer->get_found_rows($search);

		$data_rows = array();
		foreach($customers->result() as $person)
		{
			$data_rows[] = get_person_data_row($person, $this);
		}
		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Customer->get_search_suggestions($this->input->get('term'), TRUE);

		echo json_encode($suggestions);
	}

	function suggest_search()
	{
		$suggestions = $this->Customer->get_search_suggestions($this->input->post('term'), FALSE);

		echo json_encode($suggestions);
	}
	
	/*
	Loads the customer edit form
	*/
	function view($customer_id=-1)
	{
		$data['person_info'] = $this->Customer->get_info($customer_id);
		$data['total'] = $this->Customer->get_totals($customer_id)->total;

		$this->load->view("customers/form", $data);
	}
	
	/*
	Inserts/updates a customer
	*/
	function save($customer_id=-1)
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
		$customer_data=array(
			'account_number'=>$this->input->post('account_number') == '' ? null : $this->input->post('account_number'),
			'company_name'=>$this->input->post('company_name') == '' ? null : $this->input->post('company_name'),
			'discount_percent'=>$this->input->post('discount_percent') == '' ? 0.00 : $this->input->post('discount_percent'),
			'taxable'=>$this->input->post('taxable') != null
		);
		if($this->Customer->save_customer($person_data,$customer_data,$customer_id))
		{
			//New customer
			if($customer_id==-1)
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('customers_successful_adding').' '.
				$person_data['first_name'].' '.$person_data['last_name'], 'id' => $customer_data['person_id']));
			}
			else //previous customer
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('customers_successful_updating').' '.
				$person_data['first_name'].' '.$person_data['last_name'], 'id' => $customer_id));
			}
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('customers_error_adding_updating').' '.
			$person_data['first_name'].' '.$person_data['last_name'], 'id' => -1));
		}
	}
	
	function check_account_number()
	{
		$exists = $this->Customer->account_number_exists($this->input->post('account_number'),$this->input->post('person_id'));

		echo !$exists ? 'true' : 'false';
	}
	
	/*
	This deletes customers from the customers table
	*/
	function delete()
	{
		$customers_to_delete=$this->input->post('ids');
		
		if($this->Customer->delete_list($customers_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('customers_successful_deleted').' '.
			count($customers_to_delete).' '.$this->lang->line('customers_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('customers_cannot_be_deleted')));
		}
	}
	
	function excel()
	{
		$data = file_get_contents("import_customers.csv");
		$name = 'import_customers.csv';
		force_download($name, $data);
	}
	
	function excel_import()
	{
		$this->load->view("customers/form_excel_import", null);
	}

	function do_excel_import()
	{
		$msg = 'do_excel_import';
		$failCodes = array();
		if ($_FILES['file_path']['error']!=UPLOAD_ERR_OK)
		{
			$msg = $this->lang->line('items_excel_import_failed');
			echo json_encode( array('success'=>false,'message'=>$msg) );

			return;
		}
		else
		{
			if (($handle = fopen($_FILES['file_path']['tmp_name'], "r")) !== FALSE)
			{
				//Skip first row
				fgetcsv($handle);
				
				$i=1;
				while (($data = fgetcsv($handle)) !== FALSE) 
				{
					$person_data = array(
						'first_name'=>$data[0],
						'last_name'=>$data[1],
						'gender'=>$data[2],
						'email'=>$data[3],
						'phone_number'=>$data[4],
						'address_1'=>$data[5],
						'address_2'=>$data[6],
						'city'=>$data[7],
						'state'=>$data[8],
						'zip'=>$data[9],
						'country'=>$data[10],
						'comments'=>$data[11]
					);
					
					$customer_data = array(
						'company_name'=>$data[12],
						'discount_percent'=>$data[14],
						'taxable'=>$data[15]=='' ? 0 : 1
					);
					
					$account_number = $data[13];
					$invalidated = false;
					if ($account_number != "") 
					{
						$customer_data['account_number'] = $account_number;
						$invalidated = $this->Customer->account_number_exists($account_number);
					}
					
					if($invalidated || !$this->Customer->save_customer($person_data, $customer_data))
					{	
						$failCodes[] = $i;
					}
					
					$i++;
				}
			}
			else 
			{
				echo json_encode( array('success'=>false, 'message'=>'Your upload file has no data or not in supported format.') );

				return;
			}
		}

		$success = true;
		if(count($failCodes) > 0)
		{
			$msg = "Most customers imported. But some were not, here is list of their CODE (" .count($failCodes) ."): ".implode(", ", $failCodes);
			$success = false;
		}
		else
		{
			$msg = "Import Customers successful";
		}

		echo json_encode( array('success'=>$success, 'message'=>$msg) );
	}
}
?>