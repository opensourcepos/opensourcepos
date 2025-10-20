<?php

use Config\Database;

/**
 * Migration helper.
 * @param string $path Path to migration script.
 * @return bool Whether the migration executed successfully.
 */
function execute_script(string $path): bool
{
    $version = preg_replace("/(.*_)?(.*).sql/", "$2", $path);
    log_message('info', "Migrating to $version (file: $path)");

    $sql = file_get_contents($path);
    $sqls = explode(';', $sql);
    array_pop($sqls);

    $db = Database::connect();

    $success = true; // whether *all* queries succeeded
    foreach ($sqls as $statement) {
        $statement = "$statement;";
        $hadError = !$db->simpleQuery($statement);

        if ($hadError) {
            $success = false;
            foreach ($db->error() as $error) {
                log_message('error', "error: $error");
            }
        }
    }

    if ($success) {
        log_message('info', "Successfully migrated to $version");
    }
    else {
        log_message('info', "Could not migrate to $version.");
    }

    return $success;
}

/**
 * Migration helper that uses a transaction.
 * @param string $path Path to migration script.
 * @return bool Whether the migration executed successfully.
 */
function executeScriptWithTransaction(string $path): bool
{
    $version = preg_replace("/(.*_)?(.*).sql/", "$2", $path);
    log_message('info', "Migrating to $version (file: $path) with transaction");

    $sql = file_get_contents($path);
    $sqls = explode(';', $sql);
    array_pop($sqls);

    $db = Database::connect();
    $db->transStart();

    $success = true;
    try {
        foreach ($sqls as $statement) {
            $statement = "$statement;";
            $hadError = !$db->query($statement);

            if ($hadError) {
                $success = false;
                foreach ($db->error() as $error) {
                    log_message('info', "error: $error");
                }
            }
        }
    } catch (Exception $e) {
        log_message('info', "Could not migrate to $version: " . $e->getMessage());
        $db->transRollback();
        return false;
    }

    if ($success) {
        log_message('info', "Successfully migrated to $version");
    } else {
        log_message('info', "Could not migrate to $version.");
    }

    $db->transComplete();
    return $success;
}

/**
 * Drops provided foreign key constraints from given table.
 * This is required to successfully create the generated unique constraint.
 *
 * @param array $foreignKeys names of the foreign key constraints to drop
 * @param string $table name of the table the foreign key constraints are on
 * @return void
 */
function dropForeignKeyConstraints(array $foreignKeys, string $table): void
{
    $db = Database::connect();
    $forge = Database::forge();

    foreach ($foreignKeys as $fk) {
        if(foreignKeyExists($fk, $table)) {
            $forge->dropForeignKey($table, $fk);
        }
    }
}


/**
 * Removes the database prefix from the current database connection.
 * TODO: This function should be moved to a more global location since it may be needed outside of migrations.
 * @return string The prefix before overriding.
 */
function overridePrefix(string $prefix = ''): string {
    $db = Database::connect();

    $originalPrefix = $db->getPrefix();
    $db->setPrefix($prefix);

    return $originalPrefix;
}

/**
 * Creates a primary key on the specified table and index column.
 *
 * @param string $table
 * @param string $index
 * @return void
 */
function createPrimaryKey(string $table, string $index): void {
    if (! primaryKeyExists($table)) {
        $constraints = dropAllForeignKeyConstraints($table, $index);
        deleteIndex($table, $index);
        $forge = Database::forge();

        if (isMariaDb()) {
            $forge->addPrimaryKey($index);
        } else {
            $forge->addPrimaryKey($index, 'PRIMARY');
        }

        $forge->processIndexes($table);
        recreateForeignKeyConstraints($constraints);
    }
}

/**
 * Drops all foreign key constraints that reference the provided table and column.
 *
 * @param string $table
 * @param string $column
 * @return array containing the deleted constraints in case they need to be recreated after.
 */

function dropAllForeignKeyConstraints(string $table, string $column): array {
    $db = Database::connect();
    $result = $db->query("
            SELECT DISTINCT
                kcu.CONSTRAINT_NAME,
                kcu.TABLE_NAME,
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                rc.DELETE_RULE,
                rc.UPDATE_RULE
            FROM information_schema.KEY_COLUMN_USAGE kcu
            LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND kcu.TABLE_NAME = rc.TABLE_NAME
            WHERE kcu.TABLE_SCHEMA = DATABASE()
                AND ((kcu.REFERENCED_TABLE_NAME = '" . $db->getPrefix() . "$table' AND kcu.REFERENCED_COLUMN_NAME = '$column')
                OR (kcu.TABLE_NAME = '" . $db->getPrefix() . "$table' AND kcu.COLUMN_NAME = '$column'))
        ");

    $deletedConstraints = [];

    foreach ($result->getResultArray() as $constraint) {
        $deletedConstraints[] = [
            'constraintName' => $constraint['CONSTRAINT_NAME'],
            'tableName' => str_replace($db->DBPrefix, '', $constraint['TABLE_NAME']),
            'columnName' => $constraint['COLUMN_NAME'],
            'referencedTable' => str_replace($db->DBPrefix, '', $constraint['REFERENCED_TABLE_NAME']),
            'referencedColumn' => $constraint['REFERENCED_COLUMN_NAME'],
            'onDelete' => $constraint['DELETE_RULE'],
            'onUpdate' => $constraint['UPDATE_RULE'],
        ];
    }

    if ($deletedConstraints) {
        $forge = Database::forge();
        foreach ($deletedConstraints as $foreignKey) {
            $forge->dropForeignKey($foreignKey['tableName'], $foreignKey['constraintName']);
        }
    }

    return $deletedConstraints;
}

/**
 * Deletes the specified index from the specified table.
 *
 * @param string $table
 * @param string $index
 * @return void
 */
function deleteIndex(string $table, string $index): void {
    if (indexExists($table, $index)) {
        $forge = Database::forge();
        $forge->dropKey($table, $index, FALSE);
    }
}

/**
 * Checks if the specified index exists on the specified table.
 *
 * @param string $table
 * @param string $index
 * @return bool
 */
function indexExists(string $table, string $index): bool {
    $db = Database::connect();
    $result = $db->query('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = \'' . $db->getPrefix() . "$table' AND index_name = '$index'");
    $row_array = $result->getRowArray();

    return $row_array && $row_array['COUNT(*)'] > 0;
}

function primaryKeyExists(string $table): bool {
    $db = Database::connect();
    $result = $db->query('SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = \'' . $db->getPrefix() . "$table' AND constraint_type = 'PRIMARY KEY'");
    $row_array = $result->getRowArray();

    return $row_array && $row_array['COUNT(*)'] > 0;
}

function recreateForeignKeyConstraints(array $constraints): void {
    if ($constraints) {
        $forge = Database::forge();
        foreach ($constraints as $constraint) {
            $forge->addForeignKey($constraint['columnName'], $constraint['referencedTable'], $constraint['referencedColumn'], $constraint['onUpdate'], $constraint['onDelete'], $constraint['constraintName']);
            $forge->processIndexes($constraint['tableName']);
        }
    }
}

/**
 * Checks if a foreign key constraint exists in the specified table.
 *
 * @param string $constraintName
 * @param string $tableName
 * @return bool true when the constraint exists, false otherwise.
 */
function foreignKeyExists(string $constraintName, string $tableName): bool {

    $prefix = overridePrefix();

    $db = Database::connect();
    $builder = $db->table('INFORMATION_SCHEMA.TABLE_CONSTRAINTS');
    $builder->select('CONSTRAINT_NAME');
    $builder->where('TABLE_SCHEMA', $db->database);
    $builder->where('TABLE_NAME', $prefix . $tableName);
    $builder->where('CONSTRAINT_TYPE', 'FOREIGN KEY');
    $builder->where('CONSTRAINT_NAME', $constraintName);
    $query = $builder->get();

    overridePrefix($prefix);

    return $query->getNumRows() > 0;
}

/**
 * Drops a column from a table if it exists.
 *
 * @param string $table The name of the table.
 * @param string $column The name of the column to drop.
 * @return void
 */
function dropColumnIfExists(string $table, string $column): void
{
    $prefix = overridePrefix();

    $db = Database::connect();
    $builder = $db->table('information_schema.COLUMNS');

    // Check if the column exists in the table
    $builder->select('COLUMN_NAME')
        ->where('TABLE_SCHEMA', $db->database)
        ->where('TABLE_NAME', $prefix . $table)
        ->where('COLUMN_NAME', $column);

    $query = $builder->get();

    if ($query->getNumRows() > 0)
    {
        // Drop the column if it exists
        $db->query("ALTER TABLE `" . $prefix . "$table` DROP COLUMN `$column`");
    }
    overridePrefix($prefix);
}

/**
 * Checks if the current database is MariaDB.
 *
 * @return bool true if the database is MariaDB, false otherwise.
 */
function isMariaDb(): bool
{
    $db = Database::connect();
    $version = $db->getVersion();

    return stripos($version, 'mariadb') !== false;
}
