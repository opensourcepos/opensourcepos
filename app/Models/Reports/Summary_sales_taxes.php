<?php

namespace App\Models\Reports;

use Config\OSPOS;

class Summary_sales_taxes extends Summary_report
{
	private array $config;

	public function __construct()
	{
		parent::__construct();
		$this->config = config(OSPOS::class)->settings;
	}

	/**
	 * @return array[]
	 */
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

	/**
	 * @param array $inputs
	 * @param object $builder
	 * @return void
	 */
	protected function _where(array $inputs, object &$builder): void	//TODO: hungarian notation
	{
		$builder->where('sales.sale_status', COMPLETED);

		if(empty($this->config['date_or_time_format']))	//TODO: Duplicated code
		{
			$builder->where('DATE(sales.sale_time) BETWEEN ' . $this->db->escape($inputs['start_date']) . ' AND ' . $this->db->escape($inputs['end_date']));
		}
		else
		{
			$builder->where('sales.sale_time BETWEEN ' . $this->db->escape(rawurldecode($inputs['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($inputs['end_date'])));
		}
	}

	/**
	 * @param array $inputs
	 * @return array
	 */
	public function getData(array $inputs): array
	{
		$builder = $this->db->table('sales_taxes');

		if(empty($this->config['date_or_time_format']))
		{
			$builder->where('DATE(sale_time) BETWEEN ' . $inputs['start_date'] . ' AND ' . $inputs['end_date']);
		}
		else
		{
			$builder->where('sale_time BETWEEN ' . rawurldecode($inputs['start_date']) . ' AND ' . rawurldecode($inputs['end_date']));
		}

		$builder->select('reporting_authority, jurisdiction_name, tax_category, tax_rate, SUM(sale_tax_amount) AS tax');
		$builder->join('sales', 'sales_taxes.sale_id = sales.sale_id', 'left');
		$builder->join('tax_categories', 'sales_taxes.tax_category_id = tax_categories.tax_category_id', 'left');
		$builder->join('tax_jurisdictions', 'sales_taxes.jurisdiction_id = tax_jurisdictions.jurisdiction_id', 'left');
		$builder->groupBy('reporting_authority, jurisdiction_name, tax_category, tax_rate');

		$query = $builder->get();

		return $query->getResultArray();
	}
}
