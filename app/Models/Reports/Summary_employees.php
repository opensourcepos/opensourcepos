<?php

namespace App\Models\Reports;

use Config\OSPOS;

class Summary_employees extends Summary_report
{
    /**
     * @return array[]
     */
    protected function _get_data_columns(): array    // TODO: Hungarian notation
    {
        $secondaryCurrency = secondary_currency_context(config(OSPOS::class)->settings);
        $columns = [
            ['employee_name' => lang('Reports.employee')],
            ['sales'         => lang('Reports.sales'), 'sorter' => 'number_sorter'],
            ['quantity'      => lang('Reports.quantity'), 'sorter' => 'number_sorter'],
            ['subtotal'      => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
            ['tax'           => lang('Reports.tax'), 'sorter' => 'number_sorter'],
            ['total'         => lang('Reports.total'), 'sorter' => 'number_sorter'],
            ['cost'          => lang('Reports.cost'), 'sorter' => 'number_sorter'],
            ['profit'        => lang('Reports.profit'), 'sorter' => 'number_sorter']
        ];

        if ($secondaryCurrency['show']) {
            $columns[] = ['secondary_rate' => lang('Reports.selling_rate'), 'sorter' => 'number_sorter'];
            $columns[] = ['total_secondary_currency' => secondary_currency_display_label(lang('Reports.total'), $secondaryCurrency), 'sorter' => 'number_sorter'];
        }

        return $columns;
    }

    /**
     * @param array $inputs
     * @param object $builder
     * @return void
     */
    protected function _select(array $inputs, object &$builder): void    // TODO: hungarian notation
    {
        parent::_select($inputs, $builder);

        $builder->select('
                MAX(CONCAT(employee_p.first_name, " ", employee_p.last_name)) AS employee,
                SUM(sales_items.quantity_purchased) AS quantity_purchased,
                COUNT(DISTINCT sales.sale_id) AS sales
        ');
    }

    /**
     * @param object $builder
     * @return void
     */
    protected function _from(object &$builder): void    // TODO: hungarian notation
    {
        parent::_from($builder);

        $builder->join('people AS employee_p', 'sales.employee_id = employee_p.person_id');
    }

    /**
     * @param object $builder
     * @return void
     */
    protected function _group_order(object &$builder): void    // TODO: hungarian notation
    {
        $builder->groupBy('sales.employee_id');
        $builder->orderBy('employee_p.last_name');
    }
}
