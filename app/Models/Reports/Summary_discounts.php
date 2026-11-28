<?php

namespace App\Models\Reports;

use Config\OSPOS;

class Summary_discounts extends Summary_report
{
    /**
     * @return array[]
     */
    protected function _get_data_columns(): array    // TODO: Hungarian notation
    {
        return [
            ['discount' => lang('Reports.discount'), 'sorter' => 'number_sorter'],
            ['count'    => lang('Reports.count')],
            ['total'    => lang('Reports.total')]
        ];
    }

    /**
     * @param array $inputs
     * @return array
     */
    public function getData(array $inputs): array
    {
        $config = config(OSPOS::class)->settings;
        $builder = $this->db->table('sales_items AS sales_items');

        if ($inputs['discount_type'] == FIXED) {
            $currency_symbol = $this->db->escape($config['currency_symbol']);
            $builder->select('SUM(sales_items.quantity_purchased * sales_items.discount) AS total, MAX(CONCAT(' . $currency_symbol . ', sales_items.discount)) AS discount, count(*) AS count');
            $builder->where('discount_type', FIXED);
        } elseif ($inputs['discount_type'] == PERCENT) {
            $builder->select('SUM(ROUND(sales_items.quantity_purchased * item_unit_price * sales_items.discount / 100.0, ' . totals_decimals() . ')) AS total, MAX(CONCAT(sales_items.discount, "%")) AS discount, count(*) AS count');
            $builder->where('discount_type', PERCENT);
        }

        $builder->where('discount >', 0);
        $builder->groupBy('sales_items.discount');
        $builder->orderBy('sales_items.discount');

        $builder->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');

        $this->_where($inputs, $builder);    // TODO: Hungarian notation

        $rows = $builder->get()->getResultArray();
        $billBuilder = $this->db->table('sales_items AS sales_items');
        $billBuilder->select('sales.bill_discount_value AS value, SUM(sales_items.bill_discount) AS total, COUNT(DISTINCT sales.sale_id) AS count');
        $billBuilder->join('sales AS sales', 'sales_items.sale_id = sales.sale_id', 'inner');
        if ((int) $inputs['discount_type'] === FIXED) {
            $billBuilder->groupStart()->where('sales.bill_discount_type', 'fixed')->orWhere('sales.bill_discount_type IS NULL', null, false)->groupEnd();
        } else {
            $billBuilder->where('sales.bill_discount_type', 'percent');
        }
        $billBuilder->where('sales_items.bill_discount !=', 0);
        $this->_where($inputs, $billBuilder);
        $billBuilder->groupBy('sales.bill_discount_value');
        foreach ($billBuilder->get()->getResultArray() as $bill) {
            $label = lang('Sales.bill_discount');
            if ($bill['value'] !== null) {
                $amount = (int) $inputs['discount_type'] === FIXED ? to_currency($bill['value']) : to_decimals($bill['value']) . '%';
                $label .= ' (' . $amount . ')';
            }
            $rows[] = ['discount' => $label, 'count' => $bill['count'], 'total' => $bill['total']];
        }

        return $rows;
    }
}
