<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class Migration_add_consignment_support extends Migration
{
    public function up(): void
    {
        // Add consignment flags to items
        $this->forge->addColumn('items', [
            'is_consignment' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'supplier_id',
            ],
            'consignment_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
                'null'       => true,
                'default'    => null,
                'after'      => 'is_consignment',
            ],
        ]);

        // Flag suppliers that support consignment and a default rate
        $this->forge->addColumn('suppliers', [
            'is_consignor' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'category',
            ],
            'default_consignment_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
                'default'    => 0,
                'null'       => false,
                'after'      => 'is_consignor',
            ],
        ]);

        // Create consignment transactions table
        $this->forge->addField([
            'consignment_id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sale_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'sale_line' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'supplier_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'location_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,3',
                'default'    => 0,
                'null'       => false,
            ],
            'sale_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'null'       => false,
            ],
            'payout_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '7,4',
                'default'    => 0,
                'null'       => false,
            ],
            'payout_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
                'null'       => false,
            ],
            'payout_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sold_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('consignment_id', true);
        $this->forge->addKey('sale_id');
        $this->forge->addKey('supplier_id');
        $this->forge->addKey('status');
        $this->forge->addKey('sold_at');
        $this->forge->createTable('consignment_transactions', true);

        // Register consignment module and permissions
        $this->db->table('modules')->insert([
            'module_id'     => 'consignments',
            'name_lang_key' => 'module_consignments',
            'desc_lang_key' => 'module_consignments_desc',
            'sort'          => 120,
        ]);

        $this->db->table('permissions')->insert([
            'permission_id' => 'consignments',
            'module_id'     => 'consignments',
        ]);

        $this->db->table('grants')->insert([
            'permission_id' => 'consignments',
            'person_id'     => 1,
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('items', 'is_consignment');
        $this->forge->dropColumn('items', 'consignment_rate');

        $this->forge->dropColumn('suppliers', 'is_consignor');
        $this->forge->dropColumn('suppliers', 'default_consignment_rate');

        $this->forge->dropTable('consignment_transactions', true);

        $this->db->table('grants')->delete(['permission_id' => 'consignments']);
        $this->db->table('permissions')->delete(['permission_id' => 'consignments']);
        $this->db->table('modules')->delete(['module_id' => 'consignments']);
    }
}
