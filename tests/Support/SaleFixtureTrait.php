<?php

namespace Tests\Support;

use App\Models\Sale;
use Config\Database;

trait SaleFixtureTrait
{
    /**
     * Inserts a minimal completed sale row directly, bypassing Sale::save_value()
     * (which requires a full cart/inventory/tax pipeline unrelated to authorization
     * checks). Sale::get_info() inner-joins sales_items, so a matching item/
     * sales_items row is required for the sale to be found.
     */
    protected function createSale(int $employeeId): int
    {
        $unique = uniqid();
        $db = Database::connect();

        $db->table('items')->insert([
            'name'        => "Test Item $unique",
            'category'    => 'Test',
            'description' => 'Test item',
            'cost_price'  => 1,
            'unit_price'  => 1,
            'item_number' => "TEST-$unique",
        ]);
        $itemId = (int) $db->insertID();

        $db->table('sales')->insert([
            'sale_time'      => date('Y-m-d H:i:s'),
            'customer_id'    => null,
            'employee_id'    => $employeeId,
            'comment'        => 'test sale',
            'invoice_number' => null,
        ]);
        $saleId = (int) $db->insertID();

        $db->table('sales_items')->insert([
            'sale_id'            => $saleId,
            'item_id'            => $itemId,
            'line'               => 1,
            'quantity_purchased' => 1,
            'item_cost_price'    => 1,
            'item_unit_price'    => 1,
            'item_location'      => 1,
        ]);

        return $saleId;
    }

    /**
     * Same as createSale(), but flips the sale to SUSPENDED afterward so
     * postUnsuspend's status check can be exercised.
     */
    protected function createSuspendedSale(int $employeeId): int
    {
        $saleId = $this->createSale($employeeId);

        model(Sale::class)->update_sale_status($saleId, SUSPENDED);

        return $saleId;
    }
}
