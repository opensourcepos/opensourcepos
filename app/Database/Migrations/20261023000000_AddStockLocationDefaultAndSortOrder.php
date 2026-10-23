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
                'null'       => true,
                'default'    => null,
                'after'      => 'is_default',
            ],
        ]);

        // Backfill sort_order for non-deleted rows only, ranking alphabetically so upgraded
        // installs see no visible reorder. Deleted rows get NULL: they don't occupy a sort
        // position, so the active range always stays a dense 0..N-1 regardless of how many
        // locations have been deleted over the life of the install.
        $this->db->query("
            UPDATE $table AS destination
            INNER JOIN (
                SELECT
                    location_id,
                    ROW_NUMBER() OVER (ORDER BY location_name ASC, location_id ASC) - 1 AS new_sort_order
                FROM $table
                WHERE deleted = 0
            ) AS ranked ON ranked.location_id = destination.location_id
            SET destination.sort_order = ranked.new_sort_order
        ");

        $this->db->query("UPDATE $table SET sort_order = NULL WHERE deleted = 1");

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
