<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Database;

class TestDatabaseBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('Database');
        $group  = $config->tests;
        $dbName = $group['database'];
        $serverConn = Database::connect([
            'hostname' => $group['hostname'],
            'username' => $group['username'],
            'password' => $group['password'],
            'DBDriver' => $group['DBDriver'],
            'database' => null,
            'charset'  => $group['charset'] ?? 'utf8mb4',
            'DBCollat' => $group['DBCollat'] ?? 'utf8mb4_general_ci',
        ], false);

        $serverConn->query("DROP DATABASE IF EXISTS `{$dbName}`");
        $serverConn->query("CREATE DATABASE IF NOT EXISTS `{$dbName}`");

        $db = Database::connect($group, false);

        $sqlFile = APPPATH . 'Database/database.sql';
        if (! file_exists($sqlFile)) {
            throw new \RuntimeException("SQL file not found: {$sqlFile}");
        }

        $sql = file_get_contents($sqlFile);

        foreach (explode(";", $sql) as $statement) {
            $trim = trim($statement);
            if ($trim !== '') {
                $db->query($trim);
            }
        }
    }
}
