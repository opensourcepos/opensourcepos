<?php

namespace App\Models\Reports;

class Summary_categories extends Summary_report
{
    /**
     * @return array[]
     */
    protected function _get_data_columns(): array    // TODO: Hungarian notation
    {
        return [
            ['category' => lang('Reports.category')],
            ['quantity' => lang('Reports.quantity'), 'sorter' => 'number_sorter'],
            ['subtotal' => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
            ['tax'      => lang('Reports.tax'), 'sorter' => 'number_sorter'],
            ['total'    => lang('Reports.total'), 'sorter' => 'number_sorter'],
            ['cost'     => lang('Reports.cost'), 'sorter' => 'number_sorter'],
            ['profit'   => lang('Reports.profit'), 'sorter' => 'number_sorter']
        ];
    }

    /**
     * @param array $inputs
     * @param $builder
     * @return void
     */
    protected function _select(array $inputs, &$builder): void    // TODO: Hungarian notation
    {
        parent::_select($inputs, $builder);    // TODO: hungarian notation

        $builder->select('
            items.category AS category,
            SUM(sales_items.quantity_purchased) AS quantity_purchased
        ');
    }

    /**
     * @param $builder
     * @return void
     */
    protected function _from(&$builder): void    // TODO: hungarian notation
    {
        parent::_from($builder);

        $builder->join('items AS items', 'sales_items.item_id = items.item_id', 'inner');
    }

    /**
     * @param $builder
     * @return void
     */
    protected function _group_order(&$builder): void    // TODO: hungarian notation
    {
        $builder->groupBy('category');
        $builder->orderBy('category');
    }
}
