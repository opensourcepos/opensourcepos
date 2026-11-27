<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSaleItemName extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('sales_items', [
            'sale_item_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('sales_items', 'sale_item_name');
    }
}
