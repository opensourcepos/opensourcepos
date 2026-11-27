<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBillDiscountAllocation extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('sales_items', [
            'bill_discount' => ['type' => 'DECIMAL', 'constraint' => '19,4', 'default' => 0],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('sales_items', 'bill_discount');
    }
}
