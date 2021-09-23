<?php

namespace App\Controllers;

class Tax_jurisdictions extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('tax_jurisdictions');
	}


	public function index()
	{
		 $data['table_headers'] = $this->xss_clean(get_tax_jurisdictions_table_headers());

		 echo view('taxes/tax_jurisdictions', $data);
	}

	/*
	 * Returns tax_category table data rows. This will be called with AJAX.
	 */
	public function search()
	{
		$search = $this->request->getGet('search');
		$limit  = $this->request->getGet('limit');
		$offset = $this->request->getGet('offset');
		$sort   = $this->request->getGet('sort');
		$order  = $this->request->getGet('order');

		$tax_jurisdictions = $this->Tax_jurisdiction->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->Tax_jurisdiction->get_found_rows($search);

		$data_rows = [];
		foreach($tax_jurisdictions->getResult() as $tax_jurisdiction)
		{
			$data_rows[] = $this->xss_clean(get_tax_jurisdiction_data_row($tax_jurisdiction));
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows));
	}

	public function get_row($row_id)
	{
		$data_row = $this->xss_clean(get_tax_jurisdiction_data_row($this->Tax_jurisdiction->get_info($row_id)));

		echo json_encode($data_row);
	}

	public function view($tax_jurisdiction_id = -1)
	{
		$data['tax_jurisdiction_info'] = $this->Tax_jurisdiction->get_info($tax_jurisdiction_id);

		echo view("taxes/tax_jurisdiction_form", $data);
	}


	public function save($jurisdiction_id = -1)
	{
		$tax_jurisdiction_data = [
			'jurisdiction_name' => $this->request->getPost('jurisdiction_name'),
			'reporting_authority' => $this->request->getPost('reporting_authority')
		);

		if($this->Tax_jurisdiction->save($tax_jurisdiction_data))
		{
			$tax_jurisdiction_data = $this->xss_clean($tax_jurisdiction_data);

			if($jurisdiction_id == -1)
			{
				echo json_encode (['success' => TRUE, 'message' => lang('Tax_jurisdictions.successful_adding'), 'id' => $tax_jurisdiction_data['jurisdiction_id']));
			}
			else
			{
				echo json_encode (['success' => TRUE, 'message' => lang('Tax_jurisdictions.successful_updating'), 'id' => $jurisdiction_id));
			}
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Tax_jurisdictions.error_adding_updating') . ' ' . $tax_jurisdiction_data['jurisdiction_name'], 'id' => -1));
		}
	}

	public function delete()
	{
		$tax_jurisdictions_to_delete = $this->request->getPost('ids');

		if($this->Tax_jurisdiction->delete_list($tax_jurisdictions_to_delete))
		{
			echo json_encode (['success' => TRUE, 'message' => lang('Tax_jurisdictions.successful_deleted') . ' ' . count($tax_jurisdictions_to_delete) . ' ' . lang('Tax_jurisdictions.one_or_multiple')));
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Tax_jurisdictions.cannot_be_deleted')));
		}
	}
}
?>
