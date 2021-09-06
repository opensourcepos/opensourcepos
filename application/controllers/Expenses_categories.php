<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Expenses_categories extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('expenses_categories');
	}

	public function index()
	{
		 $data['table_headers'] = $this->xss_clean(get_expense_category_manage_table_headers());

		 $this->load->view('expenses_categories/manage', $data);
	}

	/*
	Returns expense_category_manage table data rows. This will be called with AJAX.
	*/
	public function search()
	{
		$search = $this->input->get('search');
		$limit  = $this->input->get('limit');
		$offset = $this->input->get('offset');
		$sort   = $this->input->get('sort');
		$order  = $this->input->get('order');

		$expense_categories = $this->Expense_category->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Expense_category->get_found_rows($search);

		$data_rows = array();
		foreach($expense_categories->result() as $expense_category)
		{
			$data_rows[] = $this->xss_clean(get_expense_category_data_row($expense_category));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_expense_category_data_row($this->Expense_category->get_info($row_id)));

		echo json_encode($data_row);
	}

	public function view($expense_category_id = -1)
	{
		$data['category_info'] = $this->Expense_category->get_info($expense_category_id);

		$this->load->view("expenses_categories/form", $data);
	}

	public function save($expense_category_id = -1)
	{
		$expense_category_data = array(
			'category_name' => $this->input->post('category_name'),
			'category_description' => $this->input->post('category_description')
		);

		if($this->Expense_category->save($expense_category_data, $expense_category_id))
		{
			$expense_category_data = $this->xss_clean($expense_category_data);

			// New expense_category_id
			if($expense_category_id == -1)
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('expenses_categories_successful_adding'), 'id' => $expense_category_data['expense_category_id']));
			}
			else // Existing Expense Category
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('expenses_categories_successful_updating'), 'id' => $expense_category_id));
			}
		}
		else//failure
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('expenses_categories_error_adding_updating') . ' ' . $expense_category_data['category_name'], 'id' => -1));
		}
	}

	public function delete()
	{
		$expense_category_to_delete = $this->input->post('ids');

		if($this->Expense_category->delete_list($expense_category_to_delete))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('expenses_categories_successful_deleted') . ' ' . count($expense_category_to_delete) . ' ' . $this->lang->line('expenses_categories_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('expenses_categories_cannot_be_deleted')));
		}
	}
}
?>
