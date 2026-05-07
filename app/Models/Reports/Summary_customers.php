<?php

namespace App\Models\Reports;

use Config\OSPOS;

class Summary_customers extends Summary_report
{
    /**
     * @return array[]
     */
    protected function _get_data_columns(): array    // TODO: Hungarian notation
    {
        $secondaryCurrency = secondary_currency_context(config(OSPOS::class)->settings);
        $columns = [
            ['customer_name' => lang('Reports.customer')],
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
    protected function _select(array $inputs, object &$builder): void    // TODO: Hungarian notation
    {
        parent::_select($inputs, $builder);    // TODO: Hungarian notation

        $builder->select('
                MAX(CONCAT(customer_p.first_name, " ", customer_p.last_name)) AS customer,
                SUM(sales_items.quantity_purchased) AS quantity_purchased,
                COUNT(DISTINCT sales.sale_id) AS sales
        ');
    }

    /**
     * @param object $builder
     * @return void
     */
    protected function _from(object &$builder): void    // TODO: Hungarian notation
    {
        parent::_from($builder);    // TODO: Hungarian notation

        $builder->join('people AS customer_p', 'sales.customer_id = customer_p.person_id');
    }

    /**
     * @param object $builder
     * @return void
     */
    protected function _group_order(object &$builder): void    // TODO: Hungarian notation
    {
        $builder->groupBy('sales.customer_id');
        $builder->orderBy('customer_p.last_name');
    }
}
