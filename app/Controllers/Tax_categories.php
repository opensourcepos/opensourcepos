<?php

namespace App\Controllers;

require_once("Secure_Controller.php");

class Tax_categories extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('tax_categories');
	}


	public function index()
	{
		 $data['tax_categories_table_headers'] = $this->xss_clean(get_tax_categories_table_headers());

		 echo view('taxes/tax_categories', $data);
	}

	/*
	 * Returns tax_category table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$tax_categories = $this->Tax_category->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Tax_category->get_found_rows($search);

		$data_rows = array();
		foreach($tax_categories->getResult() as $tax_category)
		{
			$data_rows[] = $this->xss_clean(get_tax_category_data_row($tax_category));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_tax_category_data_row($this->Tax_category->get_info($row_id)));

		echo json_encode($data_row);
	}

	public function view($tax_category_id = -1)
	{
		$data['tax_category_info'] = $this->Tax_category->get_info($tax_category_id);

		echo view("taxes/tax_category_form", $data);
	}


	public function save($tax_category_id = -1)
	{
		$tax_category_data = array(
			'tax_category' => $this->input->post('tax_category'),
			'tax_category_code' => $this->input->post('tax_category_code'),
			'tax_group_sequence' => $this->input->post('tax_group_sequence')
		);

		if($this->Tax_category->save($tax_category_data, $tax_category_id))
		{
			$tax_category_data = $this->xss_clean($tax_category_data);

			// New tax_category_id
			if($tax_category_id == -1)
			{
				echo json_encode(array('success' => TRUE, 'message' => lang('Tax_categories.successful_adding'), 'id' => $tax_category_data['tax_category_id']));
			}
			else
			{
				echo json_encode(array('success' => TRUE, 'message' => lang('Tax_categories.successful_updating'), 'id' => $tax_category_id));
			}
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => lang('Tax_categories.error_adding_updating') . ' ' . $tax_category_data['tax_category'], 'id' => -1));
		}
	}

	public function delete()
	{
		$tax_categories_to_delete = $this->input->post('ids');

		if($this->Tax_category->delete_list($tax_categories_to_delete))
		{
			echo json_encode(array('success' => TRUE, 'message' => lang('Tax_categories.successful_deleted') . ' ' . count($tax_categories_to_delete) . ' ' . lang('Tax_categories.one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => lang('Tax_categories.cannot_be_deleted')));
		}
	}
}
?>
