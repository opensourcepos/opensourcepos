<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Persons.php");

class Suppliers extends Persons
{
	public function __construct()
	{
		parent::__construct('suppliers');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_suppliers_manage_table_headers());

		$this->load->view('people/manage', $data);
	}

	/*
	Gets one row for a supplier manage table. This is called using AJAX to update one row.
	*/
	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_supplier_data_row($this->Supplier->get_info($row_id)));
		$data_row['category'] = $this->Supplier->get_category_name($data_row['category']);

		echo json_encode($data_row);
	}
	
	/*
	Returns Supplier table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$suppliers = $this->Supplier->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Supplier->get_found_rows($search);

		$data_rows = array();
		foreach($suppliers->result() as $supplier)
		{
			$row = $this->xss_clean(get_supplier_data_row($supplier));
			$row['category'] = $this->Supplier->get_category_name($row['category']);
			$data_rows[] = $row;
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->xss_clean($this->Supplier->get_search_suggestions($this->input->get('term'), TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->Supplier->get_search_suggestions($this->input->post('term'), FALSE));

		echo json_encode($suggestions);
	}
	
	/*
	Loads the supplier edit form
	*/
	public function view($supplier_id = -1)
	{
		$info = $this->Supplier->get_info($supplier_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}
		$data['person_info'] = $info;
		$data['categories'] = $this->Supplier->get_categories();

		$this->load->view("suppliers/form", $data);
	}

	/*
	Adds Person_attributes to supplier controller
	*/

	public function person_attributes($supplier_id = -1)
	{
		$data['person_id'] = $supplier_id;


		$definition_ids = json_decode($this->input->post('definition_ids'), TRUE);


		$data['definition_values'] = $this->Person_attribute->get_person_attributes_by_person($supplier_id) + $this->Person_attribute->get_values_by_definitions($definition_ids);


		$data['definition_names'] = $this->Person_attribute->get_definition_names();



		foreach($data['definition_values'] as $definition_id => $definition_value)
		{
			$person_attribute_value = $this->Person_attribute->get_person_attribute_value($supplier_id, $definition_id);


			$person_attribute_id = (empty($person_attribute_value) || empty($person_attribute_value->person_attribute_id)) ? NULL : $person_attribute_value->person_attribute_id;
	
			$values = &$data['definition_values'][$definition_id];
			$values['person_attribute_id'] = $person_attribute_id;
			$values['person_attribute_value'] = $person_attribute_value;
			$values['selected_value'] = '';

			if ($definition_value['definition_type'] == DROPDOWN)
			{
				$values['values'] = $this->Person_attribute->get_definition_values($definition_id);
				$link_value = $this->Person_attribute->get_link_value($supplier_id, $definition_id);
				$values['selected_value'] = (empty($link_value)) ? '' : $link_value->person_attribute_id;
			}

			if (!empty($definition_ids[$definition_id]))
			{
				$values['selected_value'] = $definition_ids[$definition_id];
			}

			unset($data['definition_names'][$definition_id]);
		}

		$this->load->view('person_attributes/person', $data);
	}
	
	/*
	Inserts/updates a supplier
	*/
	public function save($supplier_id = -1)
	{
		$first_name = $this->xss_clean($this->input->post('first_name'));
		$last_name = $this->xss_clean($this->input->post('last_name'));
		$email = $this->xss_clean(strtolower($this->input->post('email')));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->input->post('gender'),
			'email' => $email,
			'phone_number' => $this->input->post('phone_number'),
			'address_1' => $this->input->post('address_1'),
			'address_2' => $this->input->post('address_2'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state'),
			'zip' => $this->input->post('zip'),
			'country' => $this->input->post('country'),
			'comments' => $this->input->post('comments')
		);

		$supplier_data = array(
			'company_name' => $this->input->post('company_name'),
			'agency_name' => $this->input->post('agency_name'),
			'category' => $this->input->post('category'),
			'account_number' => $this->input->post('account_number') == '' ? NULL : $this->input->post('account_number'),
			'tax_id' => $this->input->post('tax_id')
		);

		if($this->Supplier->save_supplier($person_data, $supplier_data, $supplier_id))
		{
			$supplier_data = $this->xss_clean($supplier_data);

			//New supplier
			if($supplier_id == -1)
			{

				// Save person attributes for new supplier

			$supplier_id = $person_data['person_id'];

			$person_attribute_links = $this->input->post('person_attribute_links') != NULL ? $this->input->post('person_attribute_links') : array();
			$person_attribute_ids = $this->input->post('person_attribute_ids');
			$this->Person_attribute->delete_link($supplier_id);

			foreach($person_attribute_links as $definition_id => $person_attribute_id)
			{
				$definition_type = $this->Person_attribute->get_info($definition_id)->definition_type;
				if($definition_type != DROPDOWN)
				{
					$person_attribute_id = $this->Person_attribute->save_value($person_attribute_id, $definition_id, $supplier_id, $person_attribute_ids[$definition_id], $definition_type);
				}
				$this->Person_attribute->save_link($supplier_id, $definition_id, $person_attribute_id);
			}
				echo json_encode(array('success' => TRUE,
								'message' => $this->lang->line('suppliers_successful_adding') . ' ' . $supplier_data['company_name'],
								'id' => $supplier_data['person_id']));
			}
			else //Existing supplier
			{

				// Update person attributes for existing supplier
			
			$person_attribute_links = $this->input->post('person_attribute_links') != NULL ? $this->input->post('person_attribute_links') : array();
			$person_attribute_ids = $this->input->post('person_attribute_ids');
			$this->Person_attribute->delete_link($supplier_id);

			foreach($person_attribute_links as $definition_id => $person_attribute_id)
			{
				$definition_type = $this->Person_attribute->get_info($definition_id)->definition_type;
				if($definition_type != DROPDOWN)
				{
					$person_attribute_id = $this->Person_attribute->save_value($person_attribute_id, $definition_id, $supplier_id, $person_attribute_ids[$definition_id], $definition_type);
				}
				$this->Person_attribute->save_link($supplier_id, $definition_id, $person_attribute_id);
			}
				echo json_encode(array('success' => TRUE,
								'message' => $this->lang->line('suppliers_successful_updating') . ' ' . $supplier_data['company_name'],
								'id' => $supplier_id));
			}
		}
		else//failure
		{
			$supplier_data = $this->xss_clean($supplier_data);

			echo json_encode(array('success' => FALSE,
							'message' => $this->lang->line('suppliers_error_adding_updating') . ' ' . 	$supplier_data['company_name'],
							'id' => -1));
		}
	}
	
	/*
	This deletes suppliers from the suppliers table
	*/
	public function delete()
	{
		$suppliers_to_delete = $this->xss_clean($this->input->post('ids'));

		if($this->Supplier->delete_list($suppliers_to_delete))
		{
			echo json_encode(array('success' => TRUE,'message' => $this->lang->line('suppliers_successful_deleted').' '.
							count($suppliers_to_delete).' '.$this->lang->line('suppliers_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE,'message' => $this->lang->line('suppliers_cannot_be_deleted')));
		}
	}
	
}
?>
