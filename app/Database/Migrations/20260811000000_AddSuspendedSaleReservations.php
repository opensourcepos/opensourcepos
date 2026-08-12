<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuspendedSaleReservations extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->prefixTable('suspended_sales_reservations') . ' (
            `sale_id` int(11) NOT NULL,
            `item_id` int(11) NOT NULL,
            `location_id` int(11) NOT NULL,
            `quantity_reserved` decimal(15,3) NOT NULL DEFAULT 0,
            PRIMARY KEY (`sale_id`, `item_id`, `location_id`),
            INDEX (`item_id`),
            INDEX (`location_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    public function down(): void
    {
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('suspended_sales_reservations'));
    }
}
