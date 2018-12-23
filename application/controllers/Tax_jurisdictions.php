<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

class Tax_jurisdictions extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('tax_jurisdictions');
	}


	public function index()
	{
		 $data['table_headers'] = $this->xss_clean(get_tax_jurisdictions_table_headers());

		 $this->load->view('taxes/tax_jurisdictions', $data);
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

		$tax_jurisdictions = $this->Tax_jurisdiction->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Tax_jurisdiction->get_found_rows($search);

		$data_rows = array();
		foreach($tax_jurisdictions->result() as $tax_jurisdiction)
		{
			$data_rows[] = $this->xss_clean(get_tax_jurisdiction_data_row($tax_jurisdiction));
		}

		echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
	}

	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_tax_jurisdiction_data_row($this->Tax_jurisdiction->get_info($row_id)));

		echo json_encode($data_row);
	}

	public function view($tax_jurisdiction_id = -1)
	{
		$data['tax_jurisdiction_info'] = $this->Tax_jurisdiction->get_info($tax_jurisdiction_id);

		$this->load->view("taxes/tax_jurisdiction_form", $data);
	}


	public function save($jurisdiction_id = -1)
	{
		$tax_jurisdiction_data = array(
			'jurisdiction_name' => $this->input->post('jurisdiction_name'),
			'reporting_authority' => $this->input->post('reporting_authority')
		);

		if($this->Tax_jurisdiction->save($tax_jurisdiction_data))
		{
			$tax_jurisdiction_data = $this->xss_clean($tax_jurisdiction_data);

			if($jurisdiction_id == -1)
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_jurisdictions_successful_adding'), 'id' => $tax_jurisdiction_data['jurisdiction_id']));
			}
			else
			{
				echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_jurisdictions_successful_updating'), 'id' => $jurisdiction_id));
			}
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('taxes_jurisdictions_error_adding_updating') . ' ' . $tax_jurisdiction_data['jurisdiction_name'], 'id' => -1));
		}
	}

	public function delete()
	{
		$tax_jurisdictions_to_delete = $this->input->post('ids');

		if($this->Tax_jurisdiction->delete_list($tax_jurisdictions_to_delete))
		{
			echo json_encode(array('success' => TRUE, 'message' => $this->lang->line('taxes_jurisdictions_successful_deleted') . ' ' . count($tax_jurisdictions_to_delete) . ' ' . $this->lang->line('taxes_jurisdictions_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success' => FALSE, 'message' => $this->lang->line('taxes_jurisdictions_cannot_be_deleted')));
		}
	}
}
?>
