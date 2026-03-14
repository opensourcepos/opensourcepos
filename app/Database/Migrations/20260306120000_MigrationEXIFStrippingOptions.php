<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class MigrationEXIFStrippingOptions extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        log_message('info', 'Migrating EXIF Stripping Options');

        $db = Database::connect();

        $configs = [
            [
                'key' => 'exif_fields_to_keep',
                'value' => 'Copyright,Orientation,Software'
            ]
        ];

        foreach ($configs as $config) {
            $existing = $db->table('app_config')
                ->where('key', $config['key'])
                ->get()
                ->getRow();

            if ($existing === null) {
                $db->table('app_config')->insert($config);
            }
        }
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $db = Database::connect();

        $db->table('app_config')
            ->where('key', 'exif_fields_to_keep')
            ->delete();
    }
}