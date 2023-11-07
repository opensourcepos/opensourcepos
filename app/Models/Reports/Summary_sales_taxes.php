<?php

namespace App\Models\Reports;

class Summary_sales_taxes extends Summary_report
{
	protected function _get_data_columns(): array	//TODO: hungarian notation
	{
		return [
			['reporting_authority' => lang('Reports.authority')],
			['jurisdiction_name' => lang('Reports.jurisdiction')],
			['tax_category' => lang('Reports.tax_category')],
			['tax_rate' => lang('Reports.tax_rate'), 'sorter' => 'number_sorter'],
			['tax' => lang('Reports.tax'), 'sorter' => 'number_sorter']
		];
	}

	protected function _where(array $inputs, object &$builder): void	//TODO: hungarian notation
	{
		$config = config(OSPOS::class)->settings;

		$builder->where('sales.sale_status', COMPLETED);

		if(empty($config['date_or_time_format']))	//TODO: Duplicated code
		{
			$builder->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$builder->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}
	}

	public function getData(array $inputs): array
	{
		$config = config(OSPOS::class)->settings;

		$where = 'WHERE sale_status = ' . COMPLETED . ' ';

		if(empty($config['date_or_time_format']))
		{
			$where .= 'AND DATE(sale_time) BETWEEN ' . $this->db->escape($inputs['start_date'])
			. ' AND ' . $this->db->escape($inputs['end_date']);
		}
		else
		{
			$where .= 'AND sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date']));
		}

		//TODO: Look into whether we can convert this to use QueryBuilder
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
