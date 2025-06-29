<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class MigrationOptimizationIndices extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        error_log('Migrating Optimization Indices');

        helper('migration');
        $forge = Database::forge();

        if (!indexExists('attribute_links', 'attribute_links_uq2')) {
            $columns = [
                'item_id',
                'receiving_id',
                'sale_id',
                'definition_id',
                'attribute_id'
            ];
            $forge->addKey($columns, false, true, 'attribute_links_uq2');
            $forge->processIndexes('attribute_links');
        }

        if (!indexExists('inventory', 'trans_items_trans_date')) {
            $forge->addKey(['trans_items', 'trans_date'], false, false, 'trans_items_trans_date');
            $forge->processIndexes('inventory');
        }

        error_log('Migrating Optimization Indices');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
