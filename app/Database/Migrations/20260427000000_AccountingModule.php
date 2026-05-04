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
                'type' => 'ENUM("Asset", "Liability", "Equity", "Income", "Expense")',
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
                'type' => 'ENUM("Sale", "Purchase", "Cash", "Bank", "General")',
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
        $this->forge->createTable('accounting_items');

        // Insert into modules
        $this->db->query("INSERT INTO " . $this->db->prefixTable('modules') . " (name_lang_key, desc_lang_key, sort, module_id) VALUES ('module_accounting', 'module_accounting_desc', 90, 'accounting')");
        
        // Insert into permissions
        $this->db->query("INSERT INTO " . $this->db->prefixTable('permissions') . " (permission_id, module_id) VALUES ('accounting', 'accounting')");

        // Grant permission to admin (person_id 1 is usually admin)
        $this->db->query("INSERT INTO " . $this->db->prefixTable('grants') . " (permission_id, person_id, menu_group) VALUES ('accounting', 1, 'office')");
        
        // Seed some basic data
        $this->db->query("INSERT INTO " . $this->db->prefixTable('journals') . " (name, code, type) VALUES 
            ('Sales Journal', 'SALES', 'Sale'),
            ('Purchase Journal', 'PURCH', 'Purchase'),
            ('Cash Journal', 'CASH', 'Cash'),
            ('General Journal', 'GEN', 'General')
        ");
        
        $this->db->query("INSERT INTO " . $this->db->prefixTable('accounts') . " (code, name, type) VALUES 
            ('1000', 'Cash and Cash Equivalents', 'Asset'),
            ('1100', 'Accounts Receivable', 'Asset'),
            ('1200', 'Inventory', 'Asset'),
            ('2000', 'Accounts Payable', 'Liability'),
            ('3000', 'Owner Equity', 'Equity'),
            ('4000', 'Sales Revenue', 'Income'),
            ('5000', 'Cost of Goods Sold', 'Expense'),
            ('6000', 'Operating Expenses', 'Expense')
        ");

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
