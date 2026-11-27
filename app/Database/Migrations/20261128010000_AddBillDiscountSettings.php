<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBillDiscountSettings extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('sales', [
            'bill_discount_type' => ['type' => 'VARCHAR', 'constraint' => 8, 'null' => true],
            'bill_discount_value' => ['type' => 'DECIMAL', 'constraint' => '19,4', 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('sales', ['bill_discount_type', 'bill_discount_value']);
    }
}
