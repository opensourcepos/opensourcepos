<?php

namespace App\Models\Reports;

use CodeIgniter\Model;

require_once("Summary_report.php");

class Summary_sales_taxes extends Summary_report
{
	protected function _get_data_columns()
	{
		return array(
			array('reporting_authority' => lang('Reports.authority')),
			array('jurisdiction_name' => lang('Reports.jurisdiction')),
			array('tax_category' => lang('Reports.tax_category')),
			array('tax_rate' => lang('Reports.tax_rate'), 'sorter' => 'number_sorter'),
			array('tax' => lang('Reports.tax'), 'sorter' => 'number_sorter'));
	}

	protected function _where(array $inputs)
	{
		$builder->where('sales.sale_status', COMPLETED);

		if(empty($this->config->get('date_or_time_format')))
		{
			$builder->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$builder->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}
	}

	public function getData(array $inputs)
	{
		$where = 'WHERE sale_status = ' . COMPLETED . ' ';

		if(empty($this->config->get('date_or_time_format')))
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
			FROM " . $this->db->prefixTable('sales_taxes') . " AS sales_taxes
			JOIN " . $this->db->prefixTable('sales') . " AS sales ON sales_taxes.sale_id = sales.sale_id
			JOIN " . $this->db->prefixTable('tax_categories') . " AS tax_categories ON sales_taxes.tax_category_id = tax_categories.tax_category_id
			JOIN " . $this->db->prefixTable('tax_jurisdictions') . " AS tax_jurisdictions ON sales_taxes.jurisdiction_id = tax_jurisdictions.jurisdiction_id "
			. $where .
			"GROUP BY reporting_authority, jurisdiction_name, tax_category; ");

		return $query->getResultArray();
	}
}
?>
