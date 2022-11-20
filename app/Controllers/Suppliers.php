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

	public function index(): void
	{
		$data['table_headers'] = get_suppliers_manage_table_headers();

		echo view('people/manage', $data);
	}

	/**
	 * Gets one row for a supplier manage table. This is called using AJAX to update one row.
	 * @param $row_id
	 * @return void
	 */
	public function get_row($row_id): void
	{
		$data_row = get_supplier_data_row($this->supplier->get_info($row_id));
		$data_row['category'] = $this->supplier->get_category_name($data_row['category']);

		echo json_encode($data_row);
	}

	/**
	 * Returns Supplier table data rows. This will be called with AJAX.
	 * @return void
	 */
	public function search(): void
	{
		$search = $this->request->getGet('search', FILTER_SANITIZE_STRING);
		$limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort = $this->request->getGet('sort', FILTER_SANITIZE_STRING);
		$order = $this->request->getGet('order', FILTER_SANITIZE_STRING);

		$suppliers = $this->supplier->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->supplier->get_found_rows($search);

		$data_rows = [];

		foreach($suppliers->getResult() as $supplier)
		{
			$row = get_supplier_data_row($supplier);
			$row['category'] = $this->supplier->get_category_name($row['category']);
			$data_rows[] = $row;
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	public function suggest(): void
	{
		$suggestions = $this->supplier->get_search_suggestions($this->request->getGet('term', FILTER_SANITIZE_STRING), TRUE);

		echo json_encode($suggestions);
	}

	public function suggest_search()
	{
		$suggestions = $this->supplier->get_search_suggestions($this->request->getPost('term', FILTER_SANITIZE_STRING), FALSE);

		echo json_encode($suggestions);
	}
	
	/*
	Loads the supplier edit form
	*/
	public function view(int $supplier_id = -1): void	//TODO: Replace -1 with constant
	{
		$info = $this->supplier->get_info($supplier_id);
		foreach(get_object_vars($info) as $property => $value)
		{
			$info->$property = $value;
		}
		$data['person_info'] = $info;
		$data['categories'] = $this->supplier->get_categories();

		echo view("suppliers/form", $data);
	}
	
	/*
	Inserts/updates a supplier
	*/
	public function save(int $supplier_id = -1): void	//TODO: Replace -1 with constant
	{
		$first_name = $this->request->getPost('first_name', FILTER_SANITIZE_STRING);	//TODO: Duplicate code
		$last_name = $this->request->getPost('last_name', FILTER_SANITIZE_STRING);
		$email = strtolower($this->request->getPost('email', FILTER_SANITIZE_EMAIL));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = [
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->request->getPost('gender', FILTER_SANITIZE_STRING),
			'email' => $email,
			'phone_number' => $this->request->getPost('phone_number', FILTER_SANITIZE_STRING),
			'address_1' => $this->request->getPost('address_1', FILTER_SANITIZE_STRING),
			'address_2' => $this->request->getPost('address_2', FILTER_SANITIZE_STRING),
			'city' => $this->request->getPost('city', FILTER_SANITIZE_STRING),
			'state' => $this->request->getPost('state', FILTER_SANITIZE_STRING),
			'zip' => $this->request->getPost('zip', FILTER_SANITIZE_STRING),
			'country' => $this->request->getPost('country', FILTER_SANITIZE_STRING),
			'comments' => $this->request->getPost('comments', FILTER_SANITIZE_STRING)
		];

		$supplier_data = [
			'company_name' => $this->request->getPost('company_name', FILTER_SANITIZE_STRING),
			'agency_name' => $this->request->getPost('agency_name', FILTER_SANITIZE_STRING),
			'category' => $this->request->getPost('category', FILTER_SANITIZE_STRING),
			'account_number' => $this->request->getPost('account_number') == '' ? NULL : $this->request->getPost('account_number', FILTER_SANITIZE_STRING),
			'tax_id' => $this->request->getPost('tax_id', FILTER_SANITIZE_NUMBER_INT)
		];

		if($this->supplier->save_supplier($person_data, $supplier_data, $supplier_id))
		{
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
	public function delete(): void
	{
		$suppliers_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_NUMBER_INT);

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