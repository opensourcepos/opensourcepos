<?php

namespace App\Models\Reports;

use App\Models\Item;

/**
 *
 *
 * @property item item
 *
 */
class Inventory_summary extends Report
{
    /**
     * @return array[]
     */
    public function getDataColumns(): array
    {
        return [
            ['item_name'         => lang('Reports.item_name')],
            ['item_number'       => lang('Reports.item_number')],
            ['category'          => lang('Reports.category')],
            ['quantity'          => lang('Reports.quantity')],
            ['low_sell_quantity' => lang('Reports.low_sell_quantity')],
            ['reorder_level'     => lang('Reports.reorder_level')],
            ['location_name'     => lang('Reports.stock_location')],
            ['cost_price'        => lang('Reports.cost_price'), 'sorter' => 'number_sorter'],
            ['unit_price'        => lang('Reports.unit_price'), 'sorter' => 'number_sorter'],
            ['subtotal'          => lang('Reports.sub_total_value'), 'sorter' => 'number_sorter']
        ];
    }

    /**
     * @param array $inputs
     * @return array
     */
    public function getData(array $inputs): array
    {
        $item = model(Item::class);

        $builder = $this->db->table('items AS items');
        $builder->select(
            $item->get_item_name('name') . ',
            items.item_number,
            items.category,
            item_quantities.quantity,
            (item_quantities.quantity * items.qty_per_pack) as low_sell_quantity,
            items.reorder_level,
            stock_locations.location_name,
            items.cost_price,
            items.unit_price,
            (items.cost_price * item_quantities.quantity) AS sub_total_value'
        );
        $builder->join('item_quantities AS item_quantities', 'items.item_id = item_quantities.item_id');
        $builder->join('stock_locations AS stock_locations', 'item_quantities.location_id = stock_locations.location_id');
        $builder->where('items.deleted', 0);
        $builder->where('items.stock_type', 0);
        $builder->where('stock_locations.deleted', 0);

        // Should be corresponding to the values Inventory_summary::getItemCountDropdownArray() returns
        if ($inputs['item_count'] == 'zero_and_less') {
            $builder->where('item_quantities.quantity <=', 0);
        } elseif ($inputs['item_count'] == 'more_than_zero') {
            $builder->where('item_quantities.quantity >', 0);
        }

        if ($inputs['location_id'] != 'all') {
            $builder->where('stock_locations.location_id', $inputs['location_id']);
        }

        $builder->orderBy('items.name');
        $builder->orderBy('items.qty_per_pack');

        return $builder->get()->getResultArray();
    }

    /**
     * calculates the total value of the given inventory summary by summing all sub_total_values (see Inventory_summary::getData())
     *
     * @param array $inputs expects the reports-data-array which Inventory_summary::getData() returns
     *
     * @return array
     */
    public function getSummaryData(array $inputs): array
    {
        $return = [    // TODO: This variable name should be refactored to reflect what it is... perhaps summary_data
            'total_inventory_value'   => 0,
            'total_quantity'          => 0,
            'total_low_sell_quantity' => 0,
            'total_retail'            => 0
        ];

        foreach ($inputs as $input) {
            $return['total_inventory_value'] += $input['sub_total_value'];
            $return['total_quantity'] += $input['quantity'];
            $return['total_low_sell_quantity'] += $input['low_sell_quantity'];
            $return['total_retail'] += $input['unit_price'] * $input['quantity'];
        }

        return $return;
    }

    /**
     * returns the array for the dropdown-element item-count in the form for the inventory summary-report
     *
     * @return array
     */
    public function getItemCountDropdownArray(): array
    {
        return [
            'all'            => lang('Reports.all'),
            'zero_and_less'  => lang('Reports.zero_and_less'),
            'more_than_zero' => lang('Reports.more_than_zero')
        ];
    }
}
