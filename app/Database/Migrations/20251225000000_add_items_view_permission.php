<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_add_items_view_permission extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        log_message('info', 'Starting migration: Add items_view permission.');

        // Check if items_view permission already exists
        $existing = $this->db->table('ospos_permissions')
            ->where('permission_id', 'items_view')
            ->get()
            ->getRow();

        if (!$existing) {
            // Insert the items_view permission for the default location
            $permission_data = [
                'permission_id' => 'items_view',
                'module_id'     => 'items',
                'location_id'   => 1
            ];

            $this->db->table('ospos_permissions')->insert($permission_data);
            log_message('info', 'Inserted items_view permission.');

            // Grant items_view permission to all existing employees
            $employees = $this->db->table('ospos_employees')->get()->getResult();
            foreach ($employees as $employee) {
                // Check if grant already exists
                $grant = $this->db->table('ospos_grants')
                    ->where('permission_id', 'items_view')
                    ->where('person_id', $employee->person_id)
                    ->get()
                    ->getRow();

                if (!$grant) {
                    $this->db->table('ospos_grants')->insert([
                        'permission_id' => 'items_view',
                        'person_id' => $employee->person_id
                    ]);
                }
            }
            log_message('info', 'Granted items_view permission to all employees.');
        } else {
            log_message('info', 'items_view permission already exists. Skipping migration.');
        }

        log_message('info', 'Finished migration: Add items_view permission.');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        log_message('info', 'Reverting migration: Add items_view permission.');

        // Remove items_view grants
        $this->db->table('ospos_grants')
            ->where('permission_id', 'items_view')
            ->delete();

        // Remove items_view permission
        $this->db->table('ospos_permissions')
            ->where('permission_id', 'items_view')
            ->delete();

        log_message('info', 'Reverted migration: Add items_view permission.');
    }
}
