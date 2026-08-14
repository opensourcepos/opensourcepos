<?php

namespace App\Models\Reports;

use App\Models\Item;

/**
 * Inventory by Location report.
 *
 * Returns stock quantities in a pivot layout: one row per product, one
 * column per stock location, plus a total column. Used to see at a glance
 * which branch is short so stock can be reallocated.
 */
class Inventory_by_location extends Report
{
    /**
     * Builds the header list for the pivot table.
     *
     * @param array<int, string> $locations map of location_id => location_name
     *
     * @return list<array>
     */
    public function getPivotColumns(array $locations): array
    {
        $columns = [
            ['item_name' => lang('Reports.item_name')],
            ['item_number' => lang('Reports.item_number')],
            ['category'    => lang('Reports.category')],
        ];

        foreach ($locations as $location_id => $location_name) {
            $columns[] = ['location_' . $location_id => $location_name];
        }

        $columns[] = ['total' => lang('Reports.total_quantity')];
        $columns[] = ['reorder_level' => lang('Reports.reorder_level')];

        return $columns;
    }

    /**
     * Returns the pivot data.
     *
     * @param array<int, string> $locations map of location_id => location_name
     *
     * @return list<array>
     */
    public function getPivotData(array $locations): array
    {
        $item = model(Item::class);

        $builder = $this->db->table('items AS items');
        $builder->select(
            'items.item_id,
            ' . $item->get_item_name('item_name') . ',
            items.item_number,
            items.category,
            items.reorder_level,
            item_quantities.location_id,
            item_quantities.quantity',
        );
        $builder->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id');
        $builder->where('items.deleted', 0);
        $builder->where('items.stock_type', 0);
        $builder->orderBy('items.name');

        $rows = $builder->get()->getResultArray();

        $pivot = [];

        foreach ($rows as $row) {
            $item_id = $row['item_id'];
            if (! isset($pivot[$item_id])) {
                $pivot[$item_id] = [
                    'item_name'     => $row['item_name'],
                    'item_number'   => $row['item_number'],
                    'category'      => $row['category'],
                    'reorder_level' => $row['reorder_level'],
                    'total'         => 0,
                ];
            }

            $pivot[$item_id]['location_' . $row['location_id']] = $row['quantity'];
            $pivot[$item_id]['total'] += $row['quantity'];
        }

        // Ensure every location column exists so exports have consistent columns
        foreach ($pivot as &$row) {
            foreach ($locations as $location_id => $location_name) {
                $key = 'location_' . $location_id;
                if (! isset($row[$key])) {
                    $row[$key] = 0;
                }
            }
        }
        unset($row);

        return array_values($pivot);
    }

    /**
     * Required by the abstract Report base class. Use getPivotColumns() instead.
     *
     * @return list<array>
     */
    public function getDataColumns(): array
    {
        return [];
    }

    /**
     * Required by the abstract Report base class. Use getPivotData() instead.
     *
     * @return list<array>
     */
    public function getData(array $inputs): array
    {
        return [];
    }

    public function getSummaryData(array $inputs): array
    {
        return [];
    }
}
