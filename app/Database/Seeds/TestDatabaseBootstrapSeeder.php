<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Database;

class TestDatabaseBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        if (ENVIRONMENT !== 'testing') {
            throw new \RuntimeException('TestDatabaseBootstrapSeeder can only run in the testing environment.');
        }

        $config = config('Database');
        $group  = $config->tests;
        $dbName = $group['database'];

        if ($dbName === '' || !str_contains(strtolower($dbName), 'test')) {
            throw new \RuntimeException("Refusing to reset non-test database: {$dbName}");
        }

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
        if ($sql === false) {
            throw new \RuntimeException("Unable to read SQL file: {$sqlFile}");
        }

        foreach (explode(";", $sql) as $statement) {
            $trim = trim($statement);
            if ($trim !== '') {
                $db->query($trim);
            }
        }
    }
}
