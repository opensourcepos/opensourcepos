<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;

class Migration_AccountingModule extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        // 1. Chart of Accounts Table
        $this->forge->addField([
            'account_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['Asset', 'Liability', 'Equity', 'Income', 'Expense'],
                'default' => 'Asset',
            ],
            'parent_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('account_id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('accounts');

        // 2. Journals Table
        $this->forge->addField([
            'journal_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['Sale', 'Purchase', 'Cash', 'Bank', 'General'],
                'default' => 'General',
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('journal_id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('journals');

        // 3. Accounting Entries (Headers)
        $this->forge->addField([
            'entry_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'date' => [
                'type' => 'DATETIME',
            ],
            'journal_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'ref' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'employee_id' => [
                'type' => 'INT',
                'constraint' => 10,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('entry_id', true);
        $this->forge->addKey('journal_id');
        $this->forge->createTable('accounting_entries');

        // 4. Accounting Items (Lines)
        $this->forge->addField([
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'entry_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'account_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'debit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'credit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('item_id', true);
        $this->forge->addKey('entry_id');
        $this->forge->addKey('account_id');
        $this->forge->createTable('accounting_items');

        // Insert into modules
        $this->db->table('modules')->insert([
            'name_lang_key' => 'module_accounting',
            'desc_lang_key' => 'module_accounting_desc',
            'sort' => 90,
            'module_id' => 'accounting'
        ]);
        
        // Insert into permissions
        $this->db->table('permissions')->insert([
            'permission_id' => 'accounting',
            'module_id' => 'accounting'
        ]);

        // Grant permission to admin (usually the first employee)
        $admin = $this->db->table('employees')->select('person_id')->orderBy('person_id', 'ASC')->get(1)->getRow();
        $admin_id = $admin ? $admin->person_id : 1;
        $this->db->table('grants')->insert([
            'permission_id' => 'accounting',
            'person_id' => $admin_id,
            'menu_group' => 'office'
        ]);
        
        // Seed some basic data
        $this->db->table('journals')->insertBatch([
            ['name' => 'Sales Journal', 'code' => 'SALES', 'type' => 'Sale'],
            ['name' => 'Purchase Journal', 'code' => 'PURCH', 'type' => 'Purchase'],
            ['name' => 'Cash Journal', 'code' => 'CASH', 'type' => 'Cash'],
            ['name' => 'General Journal', 'code' => 'GEN', 'type' => 'General']
        ]);
        
        $this->db->table('accounts')->insertBatch([
            ['code' => '1000', 'name' => 'Cash and Cash Equivalents', 'type' => 'Asset'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'Asset'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'Asset'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'Liability'],
            ['code' => '3000', 'name' => 'Owner Equity', 'type' => 'Equity'],
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'Income'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'Expense'],
            ['code' => '6000', 'name' => 'Operating Expenses', 'type' => 'Expense']
        ]);

    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $this->forge->dropTable('accounting_items', true);
        $this->forge->dropTable('accounting_entries', true);
        $this->forge->dropTable('journals', true);
        $this->forge->dropTable('accounts', true);
        
        $this->db->query("DELETE FROM " . $this->db->prefixTable('grants') . " WHERE permission_id = 'accounting'");
        $this->db->query("DELETE FROM " . $this->db->prefixTable('permissions') . " WHERE module_id = 'accounting'");
        $this->db->query("DELETE FROM " . $this->db->prefixTable('modules') . " WHERE module_id = 'accounting'");
    }
}
