<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class Migration_Mysql84Utf8mb4Conversion extends Migration
{
    public function up(): void
    {
        helper('migration');
        $script = APPPATH . 'Database/Migrations/sqlscripts/3.5.0_mysql84_utf8mb4_conversion.sql';
        if (!execute_script($script)) {
            throw new RuntimeException('Failed to execute utf8mb4 conversion migration: ' . $script);
        }
    }

    // Intentionally irreversible: converting back to utf8 could cause data loss for utf8mb4 characters.
    public function down(): void
    {
    }
}
