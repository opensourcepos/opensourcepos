<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Tax_codes extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('tax_codes');
	}


	public function index()
	{
		 $this->load->view('taxes/tax_codes',get_data());
	}

	public function get_data()
	{
		$data['table_headers'] = $this->xss_clean(get_tax_codes_table_headers());
		return $data;
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

		$tax_codes = $this->Tax_code->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Tax_code->get_found_rows($search);

		$data_rows = array();
		foreach($tax_codes->result() as $tax_code)
		{
			$data_rows[] = $this->xss_clean(get_tax_code_data_row($tax_code));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_tax_code_data_row($this->Tax_code->get_info($row_id)));

		echo json_encode($data_row);
	}

	public function view($tax_code_id = -1)
	{
		$data['tax_code_info'] = $this->Tax_code->get_info($tax_code_id);

		$this->load->view("taxes/tax_code_form", $data);
	}


	public function save($tax_code_id = -1)
	{
		$tax_code_data = array(
			'tax_code' => $this->input->post('tax_code'),
			'tax_code_name' => $this->input->post('tax_code_name'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state')
		);

		if($this->Tax_code->save($tax_code_data))
		{
			$tax_code_data = $this->xss_clean($tax_code_data);

			if($tax_code_id == -1)
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_codes_successful_adding'), 'id' => $tax_code_data['tax_code_id']));
			}
			else
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_codes_successful_updating'), 'id' => $tax_code_id));
			}
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('taxes_codes_error_adding_updating') . ' ' . $tax_code_data['tax_code_id'], 'id' => -1));
		}
	}

	public function delete()
	{
		$tax_codes_to_delete = $this->input->post('ids');

		if($this->Tax_code->delete_list($tax_codes_to_delete))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_codes_successful_deleted') . ' ' . count($tax_codes_to_delete) . ' ' . $this->lang->line('taxes_codes_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('taxes_codes_cannot_be_deleted')));
		}
	}
}
?>
