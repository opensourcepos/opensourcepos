<?php

namespace App\Models\Reports;

use App\Traits\Models\Reports\ReportDateFilter;
use Config\OSPOS;

class Summary_sales_taxes extends Summary_report
{
    use ReportDateFilter;

    private array $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = config(OSPOS::class)->settings;
    }

    protected function _get_data_columns(): array
    {
        return [
            ['reporting_authority' => lang('Reports.authority')],
            ['jurisdiction_name'   => lang('Reports.jurisdiction')],
            ['tax_category'        => lang('Reports.tax_category')],
            ['tax_rate'            => lang('Reports.tax_rate'), 'sorter' => 'number_sorter'],
            ['tax'                 => lang('Reports.tax'), 'sorter' => 'number_sorter']
        ];
    }

    protected function _where(array $inputs, object &$builder): void
    {
        $builder->where('sales.sale_status', COMPLETED);
        $this->applyDateFilter($builder, $inputs, 'sales', 'sale_time');
    }

    public function getData(array $inputs): array
    {
        $builder = $this->db->table('sales_taxes');
        $this->applyDateFilter($builder, $inputs, 'sales_taxes', 'sale_time');

        $builder->select('reporting_authority, jurisdiction_name, tax_category, tax_rate, SUM(sale_tax_amount) AS tax');
        $builder->join('sales', 'sales_taxes.sale_id = sales.sale_id', 'left');
        $builder->join('tax_categories', 'sales_taxes.tax_category_id = tax_categories.tax_category_id', 'left');
        $builder->join('tax_jurisdictions', 'sales_taxes.jurisdiction_id = tax_jurisdictions.jurisdiction_id', 'left');
        $builder->groupBy('reporting_authority, jurisdiction_name, tax_category, tax_rate');

        $query = $builder->get();

        return $query->getResultArray();
    }
}
