<?php

use Config\Database;

/**
 * Executes a SQL migration script.
 * @param string $path Path to migration script.
 * @param bool $withTransaction Whether to wrap execution in a transaction.
 * @return bool Whether the migration executed successfully.
 */
function executeScript(string $path, bool $withTransaction = false): bool
{
    $version = explode('_', basename($path, '.sql'), 2)[0];
    log_message('info', "Migrating to $version (file: $path)");

    $sqls = explode(';', file_get_contents($path));
    array_pop($sqls);

    $db = Database::connect();

    if ($withTransaction) {
        $db->transStart();
    }

    $success = true; // whether *all* queries succeeded
    try {
        foreach ($sqls as $statement) {
            $statement = "$statement;";
            $hadError = $withTransaction ? !$db->query($statement) : !$db->simpleQuery($statement);

            if ($hadError) {
                $success = false;
                foreach ($db->error() as $error) {
                    log_message('error', "error: $error");
                }
            }
        }
    } catch (Exception $e) {
        log_message('error', "Could not migrate to $version: " . $e->getMessage());
        if ($withTransaction) {
            $db->transRollback();
        }
        return false;
    }

    if ($success) {
        log_message('info', "Successfully migrated to $version");
    } else {
        log_message('info', "Could not migrate to $version.");
    }

    if ($withTransaction) {
        $db->transComplete();
    }

    return $success;
}

/**
 * Drops provided foreign key constraints from a given table if the constraint exists.
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
 * Drops foreign key constraints that reference the provided table and column.
 * When $table and $column are omitted, drops all foreign key constraints in the schema.
 *
 * @param string|null $table
 * @param string|null $column
 * @return array containing the deleted constraints in case they need to be recreated after.
 */

function dropAllForeignKeyConstraints(?string $table = null, ?string $column = null): array {
    $db = Database::connect();
    $prefixedTable = $table !== null ? $db->getPrefix() . $table : null;
    $prefix = overridePrefix();

    $builder = $db->table('information_schema.KEY_COLUMN_USAGE kcu');
    $builder->distinct();
    $builder->select('kcu.CONSTRAINT_NAME, kcu.TABLE_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME, kcu.ORDINAL_POSITION, rc.DELETE_RULE, rc.UPDATE_RULE');
    $builder->join(
        'information_schema.REFERENTIAL_CONSTRAINTS rc',
        'kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA AND kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND kcu.TABLE_NAME = rc.TABLE_NAME',
        'left'
    );

    if ($table !== null && $column !== null) {
        $scopedBuilder = $db->table('information_schema.KEY_COLUMN_USAGE scoped');
        $scopedBuilder->distinct();
        $scopedBuilder->select('scoped.CONSTRAINT_NAME, scoped.TABLE_NAME');
        $scopedBuilder->where('scoped.TABLE_SCHEMA', $db->database);
        $scopedBuilder->groupStart();
        $scopedBuilder->where('scoped.REFERENCED_TABLE_NAME', $prefixedTable);
        $scopedBuilder->where('scoped.REFERENCED_COLUMN_NAME', $column);
        $scopedBuilder->groupEnd();
        $scopedBuilder->orGroupStart();
        $scopedBuilder->where('scoped.TABLE_NAME', $prefixedTable);
        $scopedBuilder->where('scoped.COLUMN_NAME', $column);
        $scopedBuilder->groupEnd();

        $builder->join(
            '(' . $scopedBuilder->getCompiledSelect() . ') matched',
            'matched.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME AND matched.TABLE_NAME = kcu.TABLE_NAME',
            'inner',
            false
        );
    }

    $builder->where('kcu.TABLE_SCHEMA', $db->database);
    $builder->where('rc.CONSTRAINT_NAME IS NOT NULL', null, false);
    $builder->orderBy('kcu.CONSTRAINT_NAME');
    $builder->orderBy('kcu.ORDINAL_POSITION');

    $result = $builder->get();
    overridePrefix($prefix);

    $deletedConstraints = [];

    foreach ($result->getResultArray() as $constraint) {
        $key = $constraint['TABLE_NAME'] . '.' . $constraint['CONSTRAINT_NAME'];

        if (!isset($deletedConstraints[$key])) {
            $deletedConstraints[$key] = [
                'constraintName' => $constraint['CONSTRAINT_NAME'],
                'tableName' => str_replace($db->DBPrefix, '', $constraint['TABLE_NAME']),
                'columnName' => [],
                'referencedTable' => str_replace($db->DBPrefix, '', $constraint['REFERENCED_TABLE_NAME']),
                'referencedColumn' => [],
                'onDelete' => $constraint['DELETE_RULE'],
                'onUpdate' => $constraint['UPDATE_RULE'],
                'seenPositions' => [],
            ];
        }

        $position = $constraint['ORDINAL_POSITION'];
        if (!isset($deletedConstraints[$key]['seenPositions'][$position])) {
            $deletedConstraints[$key]['seenPositions'][$position] = true;
            $deletedConstraints[$key]['columnName'][] = $constraint['COLUMN_NAME'];
            $deletedConstraints[$key]['referencedColumn'][] = $constraint['REFERENCED_COLUMN_NAME'];
        }
    }

    foreach ($deletedConstraints as &$constraint) {
        unset($constraint['seenPositions']);
    }
    unset($constraint);

    $deletedConstraints = array_values($deletedConstraints);

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
