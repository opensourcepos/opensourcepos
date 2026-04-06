<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_Mysql84Utf8mb4Conversion extends Migration
{
    public function up(): void
    {
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.5.0_mysql84_utf8mb4_conversion.sql');
    }

    public function down(): void
    {
    }
}
