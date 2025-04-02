<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_fix_empty_reports extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $builder = $this->db->table('stock_locations');
        $builder->select('location_name');
        $builder->where('location_id', 1);
        $builder->limit(1);
        $location_name = $builder->get()->getResultArray()[0]['location_name'];

        $location_name = str_replace(' ', '_', $location_name);
        $builder = $this->db->table('permissions');
        $builder->set('location_id', 1);
        $builder->where('permission_id', 'receivings_' . $location_name);
        $builder->orWhere('permission_id', 'sales_' . $location_name);
        $builder->update();
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}
}
