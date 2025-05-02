<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_image_upload_defaults extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $image_values = [
            ['key' => 'image_allowed_types', 'value' => 'gif|jpg|png'],
            ['key' => 'image_max_height',    'value' => '480'],
            ['key' => 'image_max_size',      'value' => '128'],
            ['key' => 'image_max_width',     'value' => '640']
        ];

        $builder = $this->db->table('app_config');
        $builder->insertBatch($image_values);
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $builder = $this->db->table('app_config');
        $builder->whereIn('key', ['image_allowed_types', 'image_max_height', 'image_max_size', 'image_max_width']);
        $builder->delete();
    }
}
