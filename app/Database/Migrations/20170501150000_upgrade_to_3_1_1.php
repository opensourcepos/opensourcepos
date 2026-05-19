<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Migration;

class Migration_Upgrade_To_3_1_1 extends Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        helper('migration');

        // MariaDB blocks CONVERT TO CHARACTER SET on tables with FK constraints.
        // Drop all FKs across affected tables before running the SQL script, recreate after.
        $fkColumns = [
            ['modules',         'module_id'],
            ['stock_locations', 'location_id'],
            ['permissions',     'permission_id'],
            ['people',          'person_id'],
            ['suppliers',       'supplier_id'],
            ['items',           'item_id'],
            ['item_kits',       'item_kit_id'],
            ['sales',           'sale_id'],
            ['receivings',      'receiving_id'],
            ['employees',       'employee_id'],
            ['customers',       'person_id'],
        ];

        $constraints = [];
        foreach ($fkColumns as [$table, $column]) {
            foreach (dropAllForeignKeyConstraints($table, $column) as $c) {
                $constraints[$c['constraintName']] = $c;
            }
        }

        if (!execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.0.2_to_3.1.1.sql')) {
            throw new DatabaseException('Migration script 3.0.2_to_3.1.1.sql failed. Check logs for details.');
        }

        $droppedTables = ['sales_suspended', 'sales_suspended_items', 'sales_suspended_items_taxes', 'sales_suspended_payments'];
        $toRecreate = array_filter($constraints, fn($c) => !in_array($c['tableName'], $droppedTables, true));
        recreateForeignKeyConstraints(array_values($toRecreate));
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
