<?php

namespace App\Models\Reports;

use App\Models\Sale;

class Detailed_item_sales extends Report
{
    public function create(array $inputs): void
    {
        $sale = model(Sale::class);
        $sale->create_temp_table($inputs);
    }

    public function getDataColumns(): array
    {
        return [
            ['sale_time'     => lang('Reports.date')],
            ['sale_id'       => lang('Reports.sale_id')],
            ['customer_name' => lang('Reports.sold_to')],
            ['item_name'     => lang('Reports.item')],
            ['category'      => lang('Reports.category')],
            ['item_number'   => lang('Reports.item_number')],
            ['quantity'      => lang('Reports.quantity'), 'sorter' => 'number_sorter'],
            ['unit_price'    => lang('Reports.unit_price'), 'sorter' => 'number_sorter'],
            ['discount'      => lang('Reports.discount'), 'sorter' => 'number_sorter'],
            ['subtotal'      => lang('Reports.subtotal'), 'sorter' => 'number_sorter'],
            ['tax'           => lang('Reports.tax'), 'sorter' => 'number_sorter'],
            ['total'         => lang('Reports.total'), 'sorter' => 'number_sorter'],
            ['cost'          => lang('Reports.cost'), 'sorter' => 'number_sorter'],
            ['profit'        => lang('Reports.profit'), 'sorter' => 'number_sorter'],
            ['employee_name' => lang('Reports.sold_by')],
            ['payment_type'  => lang('Reports.payment_type')],
            ['comment'       => lang('Reports.comments')],
        ];
    }

    public function getData(array $inputs): array
    {
        $builder = $this->db->table('sales_items_temp');
        $builder->select('
            sale_time,
            sale_id,
            customer_name,
            name AS item_name,
            category,
            item_number,
            quantity_purchased,
            item_unit_price,
            discount,
            discount_type,
            subtotal,
            tax,
            total,
            cost,
            profit,
            employee_name,
            payment_type,
            comment,
            item_location
        ');

        $this->applyFilters($inputs, $builder);
        $builder->orderBy('sale_time');
        $builder->orderBy('sale_id');
        $builder->orderBy('line');

        return $builder->get()->getResultArray();
    }

    public function getSummaryData(array $inputs): array
    {
        $builder = $this->db->table('sales_items_temp');
        $builder->select('
            SUM(quantity_purchased) AS total_quantity,
            SUM(subtotal) AS subtotal,
            SUM(tax) AS tax,
            SUM(total) AS total,
            SUM(cost) AS cost,
            SUM(profit) AS profit
        ');

        $this->applyFilters($inputs, $builder);

        return $builder->get()->getRowArray();
    }

    private function applyFilters(array $inputs, object $builder): void
    {
        switch ($inputs['sale_type']) {
            case 'complete':
                $builder->where('sale_status', COMPLETED);
                $builder->groupStart();
                $builder->where('sale_type', SALE_TYPE_POS);
                $builder->orWhere('sale_type', SALE_TYPE_INVOICE);
                $builder->orWhere('sale_type', SALE_TYPE_RETURN);
                $builder->groupEnd();
                break;

            case 'quotes':
                $builder->where('sale_status', SUSPENDED);
                $builder->where('sale_type', SALE_TYPE_QUOTE);
                break;

            case 'work_orders':
                $builder->where('sale_status', SUSPENDED);
                $builder->where('sale_type', SALE_TYPE_WORK_ORDER);
                break;

            case 'canceled':
                $builder->where('sale_status', CANCELED);
                break;

            case 'returns':
                $builder->where('sale_status', COMPLETED);
                $builder->where('sale_type', SALE_TYPE_RETURN);
                break;

            case 'sales':
            default:
                $builder->where('sale_status', COMPLETED);
                $builder->groupStart();
                $builder->where('sale_type', SALE_TYPE_POS);
                $builder->orWhere('sale_type', SALE_TYPE_INVOICE);
                $builder->groupEnd();
                break;
        }

        if (($inputs['location_id'] ?? 'all') !== 'all') {
            $builder->where('item_location', $inputs['location_id']);
        }

        if (($inputs['discount_type'] ?? 'all') !== 'all') {
            $builder->where('discount_type', $inputs['discount_type']);
        }
    }
}
