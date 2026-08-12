<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSupplierLotTracking extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->prefixTable('item_lots') . ' (
            `item_id` int(11) NOT NULL,
            `receiving_id` int(11) NOT NULL DEFAULT 0,
            `location_id` int(11) NOT NULL,
            `quantity` decimal(15,3) NOT NULL DEFAULT 0,
            PRIMARY KEY (`item_id`, `receiving_id`, `location_id`),
            INDEX (`receiving_id`),
            INDEX (`location_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->prefixTable('sales_items_lots') . ' (
            `sale_id` int(11) NOT NULL,
            `line` int(3) NOT NULL,
            `receiving_id` int(11) NOT NULL DEFAULT 0,
            `quantity` decimal(15,3) NOT NULL DEFAULT 0,
            PRIMARY KEY (`sale_id`, `line`, `receiving_id`),
            INDEX (`sale_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->db->query('INSERT INTO ' . $this->db->prefixTable('item_lots') . '
            (item_id, receiving_id, location_id, quantity)
            SELECT ri.item_id, ri.receiving_id, ri.item_location, (ri.quantity_purchased * ri.receiving_quantity)
            FROM ' . $this->db->prefixTable('receivings_items') . ' AS ri
            INNER JOIN ' . $this->db->prefixTable('receivings') . ' AS r
                ON r.receiving_id = ri.receiving_id');
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('sales_items_lots'));
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('item_lots'));
    }
}
