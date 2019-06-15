<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once("Summary_report.php");

class Summary_sales_taxes extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('reporting_authority' => $this->lang->line('reports_authority')),
			array('jurisdiction_name' => $this->lang->line('reports_jurisdiction')),
			array('tax_category' => $this->lang->line('reports_tax_category')),
			array('tax_rate' => $this->lang->line('reports_tax_rate'), 'sorter' => 'number_sorter'),
			array('tax' => $this->lang->line('reports_tax'), 'sorter' => 'number_sorter'));
	}

	protected function _where(array $inputs)
	{
		$this->db->where('sales.sale_status', COMPLETED);

		if(empty($this->config->item('date_or_time_format')))
		{
			$this->db->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$this->db->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}
	}

	public function getData(array $inputs)
	{
		$where = 'WHERE sale_status = ' . COMPLETED . ' ';

		if(empty($this->config->item('date_or_time_format')))
		{
			$where .= 'AND DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date'])
			. ' AND ' . $this->db->escape($inputs['end_date']);
		}
		else
		{
			$where .= 'AND sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}

		$query = $this->db->query("SELECT reporting_authority, jurisdiction_name, tax_category, tax_rate,
			SUM(sale_tax_amount) AS tax
			FROM " . $this->db->dbprefix('sales_taxes') . " AS sales_taxes
			JOIN " . $this->db->dbprefix('sales') . " AS sales ON sales_taxes.sale_id = sales.sale_id
			JOIN " . $this->db->dbprefix('tax_categories') . " AS tax_categories ON sales_taxes.tax_category_id = tax_categories.tax_category_id
			JOIN " . $this->db->dbprefix('tax_jurisdictions') . " AS tax_jurisdictions ON sales_taxes.jurisdiction_id = tax_jurisdictions.jurisdiction_id "
			. $where .
			"GROUP BY reporting_authority, jurisdiction_name, tax_category; ");

		return $query->result_array();
	}
}
?>
