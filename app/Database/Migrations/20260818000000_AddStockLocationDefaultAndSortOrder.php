<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStockLocationDefaultAndSortOrder extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $table = $this->db->prefixTable('stock_locations');

        $this->forge->addColumn('stock_locations', [
            'is_default' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'deleted',
            ],
            'sort_order' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
                'after'      => 'is_default',
            ],
        ]);

        // Backfill sort_order for every row (deleted included, since the column is unique table-wide),
        // ranking non-deleted rows alphabetically first so upgraded installs see no visible reorder.
        $this->db->query('SET @rank := -1');
        $this->db->query("UPDATE $table SET sort_order = (@rank := @rank + 1) ORDER BY deleted ASC, location_name ASC");

        // Default to the oldest (lowest location_id) non-deleted location so behavior is unchanged until an admin picks one explicitly.
        $this->db->query("UPDATE $table SET is_default = 1 WHERE deleted = 0 ORDER BY location_id ASC LIMIT 1");

        $this->forge->addUniqueKey('sort_order');
        $this->forge->processIndexes('stock_locations');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $this->forge->dropColumn('stock_locations', ['is_default', 'sort_order']);
    }
}
