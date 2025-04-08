<?php

namespace App\Models\Reports;

class Summary_items extends Summary_report
{
    /**
     * @return array[]
     */
    protected function _get_data_columns(): array    // TODO: Hungarian notation
    {
        return [
            ['item_name'  => lang('Reports.item')],
            ['category'   => lang('Reports.category')],
            ['cost_price' => lang('Reports.cost_price'), 'sorter' => 'number_sorter'],
            ['unit_price' => lang('Reports.unit_price'), 'sorter' => 'number_sorter'],
            ['quantity'   => lang('Reports.quantity'), 'sorter' => 'number_sorter'],
            ['subtotal'   => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
            ['tax'        => lang('Reports.tax'), 'sorter' => 'number_sorter'],
            ['total'      => lang('Reports.total'), 'sorter' => 'number_sorter'],
            ['cost'       => lang('Reports.cost'), 'sorter' => 'number_sorter'],
            ['profit'     => lang('Reports.profit'), 'sorter' => 'number_sorter']
        ];
    }

    /**
     * @param array $inputs
     * @param object $builder
     * @return void
     */
    protected function _select(array $inputs, object &$builder): void    // TODO: hungarian notation
    {
        parent::_select($inputs, $builder);    // TODO: hungarian notation

        $builder->select('
                MAX(items.name) AS name,
                MAX(items.category) AS category,
                MAX(items.cost_price) AS cost_price,
                MAX(items.unit_price) AS unit_price,
                SUM(sales_items.quantity_purchased) AS quantity_purchased
        ');
    }

    /**
     * @param object $builder
     * @return void
     */
    protected function _from(object &$builder): void    // TODO: hungarian notation
    {
        parent::_from($builder);

        $builder->join('items AS items', 'sales_items.item_id = items.item_id', 'inner');
    }

    /**
     * @param object $builder
     * @return void
     */
    protected function _group_order(object &$builder): void    // TODO: hungarian notation
    {
        $builder->groupBy('items.item_id');
        $builder->orderBy('name');
    }
}
