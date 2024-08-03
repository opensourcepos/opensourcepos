<?php

namespace App\Controllers;

use App\Models\Supplier;
use Config\Services;

class Suppliers extends Persons
{
	private Supplier $supplier;

	public function __construct()
	{
		parent::__construct('suppliers');

		$this->supplier = model(Supplier::class);
	}

	/**
	 * @return void
	 */
	public function getIndex(): void
	{
		$data['table_headers'] = get_suppliers_manage_table_headers();

		echo view('people/manage', $data);
	}

	/**
	 * Gets one row for a supplier manage table. This is called using AJAX to update one row.
	 * @param $row_id
	 * @return void
	 */
	public function getRow($row_id): void
	{
		$data_row = get_supplier_data_row($this->supplier->get_info($row_id));
		$data_row['category'] = $this->supplier->get_category_name($data_row['category']);

		echo json_encode($data_row);
	}

	/**
	 * Returns Supplier table data rows. This will be called with AJAX.
	 * @return void
	 **/
	public function getSearch(): void
	{
		$search = $this->request->getGet('search');
		$limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
		$offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
		$sort = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

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

	/**
	 * Gives search suggestions based on what is being searched for
	 **/
	public function getSuggest(): void
	{
		$search = $this->request->getGet('term');
		$suggestions = $this->supplier->get_search_suggestions($search, true);

		echo json_encode($suggestions);
	}

	/**
	 * @return void
	 */
	public function suggest_search(): void
	{
		$search = $this->request->getPost('term');
		$suggestions = $this->supplier->get_search_suggestions($search, false);

		echo json_encode($suggestions);
	}

	/**
	 * Loads the supplier edit form
	 *
	 * @param int $supplier_id
	 * @return void
	 */
	public function getView(int $supplier_id = NEW_ENTRY): void
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

	/**
	 * Inserts/updates a supplier
	 *
	 * @param int $supplier_id
	 * @return void
	 */
	public function postSave(int $supplier_id = NEW_ENTRY): void
	{
		$first_name = $this->request->getPost('first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);	//TODO: Duplicate code
		$last_name = $this->request->getPost('last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$email = strtolower($this->request->getPost('email', FILTER_SANITIZE_EMAIL));

		// format first and last name properly
		$first_name = $this->nameize($first_name);
		$last_name = $this->nameize($last_name);

		$person_data = [
			'first_name' => $first_name,
			'last_name' => $last_name,
			'gender' => $this->request->getPost('gender'),
			'email' => $email,
			'phone_number' => $this->request->getPost('phone_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'address_1' => $this->request->getPost('address_1', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'address_2' => $this->request->getPost('address_2', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'city' => $this->request->getPost('city', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'state' => $this->request->getPost('state', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'zip' => $this->request->getPost('zip', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'country' => $this->request->getPost('country', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'comments' => $this->request->getPost('comments', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
		];

		$supplier_data = [
			'company_name' => $this->request->getPost('company_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'agency_name' => $this->request->getPost('agency_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'category' => $this->request->getPost('category', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'account_number' => $this->request->getPost('account_number') == '' ? null : $this->request->getPost('account_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'tax_id' => $this->request->getPost('tax_id', FILTER_SANITIZE_NUMBER_INT)
		];

		if($this->supplier->save_supplier($person_data, $supplier_data, $supplier_id))
		{
			//New supplier
			if($supplier_id == NEW_ENTRY)
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Suppliers.successful_adding') . ' ' . $supplier_data['company_name'],
					'id' => $supplier_data['person_id']
				]);
			}
			else //Existing supplier
			{
				echo json_encode ([
					'success' => true,
					'message' => lang('Suppliers.successful_updating') . ' ' . $supplier_data['company_name'],
					'id' => $supplier_id]);
			}
		}
		else//failure
		{
			echo json_encode ([
				'success' => false,
				'message' => lang('Suppliers.error_adding_updating') . ' ' . 	$supplier_data['company_name'],
				'id' => NEW_ENTRY
			]);
		}
	}

	/**
	 * This deletes suppliers from the suppliers table
	 *
	 * @return void
	 */
	public function postDelete(): void
	{
		$suppliers_to_delete = $this->request->getPost('ids', FILTER_SANITIZE_NUMBER_INT);

		if($this->supplier->delete_list($suppliers_to_delete))
		{
			echo json_encode ([
				'success' => true,
				'message' => lang('Suppliers.successful_deleted') . ' ' . count($suppliers_to_delete) . ' ' . lang('Suppliers.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => false, 'message' => lang('Suppliers.cannot_be_deleted')]);
		}
	}
}
