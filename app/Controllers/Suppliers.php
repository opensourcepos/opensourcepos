<?php

namespace App\Controllers;

use app\Models\Supplier;

/**
 *
 *
 * @property supplier supplier
 *
 */
class Suppliers extends Persons
{
	public function __construct()
	{
		parent::__construct('suppliers');

		$this->supplier = model('Supplier');
	}

	public function index()
	{
		$data['table_headers'] = $this->xss_clean(get_suppliers_manage_table_headers());

		echo view('people/manage', $data);
	}

	/*
	Gets one row for a supplier manage table. This is called using AJAX to update one row.
	*/
	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_supplier_data_row($this->supplier->get_info($row_id)));
		$data_row['category'] = $this->supplier->get_category_name($data_row['category']);

		echo json_encode($data_row);
	}
	
	/*
	Returns Supplier table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->request->getGet('search');
		$limit = $this->request->getGet('limit');
		$offset = $this->request->getGet('offset');
		$sort = $this->request->getGet('sort');
		$order = $this->request->getGet('order');

		$suppliers = $this->supplier->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->supplier->get_found_rows($search);

		$data_rows = [];

		foreach($suppliers->getResult() as $supplier)
		{
			$row = $this->xss_clean(get_supplier_data_row($supplier));
			$row['category'] = $this->supplier->get_category_name($row['category']);
			$data_rows[] = $row;
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest()
	{
		$suggestions = $this->xss_clean($this->supplier->get_search_suggestions($this->request->getGet('term'), TRUE));

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$suggestions = $this->xss_clean($this->supplier->get_search_suggestions($this->request->getPost('term'), FALSE));

		echo json_encode($suggestions);
	}
	
	/*
	Loads the supplier edit form
	*/
	public function view(int $supplier_id = -1)	//TODO: Replace -1 with constant
	{
		$info = $this->supplier->get_info($supplier_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $this->xss_clean($value);
		}
		$data['person_info'] = $info;
		$data['categories'] = $this->supplier->get_categories();

		echo view("suppliers/form", $data);
	}
	
	/*
	Inserts/updates a supplier
	*/
	public function save(int $supplier_id = -1)	//TODO: Replace -1 with constant
	{
		$first_name = $this->xss_clean($this->request->getPost('first_name'));	//TODO: Duplicate code
		$last_name = $this->xss_clean($this->request->getPost('last_name'));
		$email = $this->xss_clean(strtolower($this->request->getPost('email')));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = [
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->request->getPost('gender'),
			'email' => $email,
			'phone_number' => $this->request->getPost('phone_number'),
			'address_1' => $this->request->getPost('address_1'),
			'address_2' => $this->request->getPost('address_2'),
			'city' => $this->request->getPost('city'),
			'state' => $this->request->getPost('state'),
			'zip' => $this->request->getPost('zip'),
			'country' => $this->request->getPost('country'),
			'comments' => $this->request->getPost('comments')
		];

		$supplier_data = [
			'company_name' => $this->request->getPost('company_name'),
			'agency_name' => $this->request->getPost('agency_name'),
			'category' => $this->request->getPost('category'),
			'account_number' => $this->request->getPost('account_number') == '' ? NULL : $this->request->getPost('account_number'),
			'tax_id' => $this->request->getPost('tax_id')
		];

		if($this->supplier->save_supplier($person_data, $supplier_data, $supplier_id))
		{
			$supplier_data = $this->xss_clean($supplier_data);

			//New supplier
			if($supplier_id == -1)	//TODO: Replace -1 with a constant
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Suppliers.successful_adding') . ' ' . $supplier_data['company_name'],
					'id' => $supplier_data['person_id']
				]);
			}
			else //Existing supplier
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Suppliers.successful_updating') . ' ' . $supplier_data['company_name'],
					'id' => $supplier_id]);
			}
		}
		else//failure
		{
			$supplier_data = $this->xss_clean($supplier_data);

			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Suppliers.error_adding_updating') . ' ' . 	$supplier_data['company_name'],
				'id' => -1	//TODO: Replace -1 with a constant
			]);
		}
	}
	
	/*
	This deletes suppliers from the suppliers table
	*/
	public function delete()
	{
		$suppliers_to_delete = $this->xss_clean($this->request->getPost('ids'));

		if($this->supplier->delete_list($suppliers_to_delete))
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Suppliers.successful_deleted') . ' ' . count($suppliers_to_delete) . ' ' . lang('Suppliers.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Suppliers.cannot_be_deleted')]);
		}
	}
	
}
?>
