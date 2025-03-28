<?php

use Config\Database;

/**
 * Migration helper
 */
function execute_script(string $path): void
{
    $version = preg_replace("/(.*_)?(.*).sql/", "$2", $path);
    error_log("Migrating to $version (file: $path)");

    $sql = file_get_contents($path);
    $sqls = explode(';', $sql);
    array_pop($sqls);

    $db = Database::connect();

    foreach($sqls as $statement)
    {
        $statement = "$statement;";

        if(!$db->simpleQuery($statement))
        {
            foreach($db->error() as $error)
            {
                error_log("error: $error");
            }
        }
    }

    error_log("Migrated to $version");
}

/**
 * Drops the foreign key constraints from the attribute_links table.
 * This is required to successfully create the generated unique constraint.
 *
 * @return void
 */
function drop_foreign_key_constraints(array $foreignKeys, string $table): void
{
    $db = Database::connect();

    $current_prefix = $db->getPrefix();
    $db->setPrefix('');
    $database_name = $db->database;

    foreach ($foreignKeys as $fk)
    {
        $builder = $db->table('INFORMATION_SCHEMA.TABLE_CONSTRAINTS');
        $builder->select('CONSTRAINT_NAME');
        $builder->where('TABLE_SCHEMA', $database_name);
        $builder->where('TABLE_NAME', $table);
        $builder->where('CONSTRAINT_TYPE', 'FOREIGN KEY');
        $builder->where('CONSTRAINT_NAME', $fk);
        $query = $builder->get();

        if($query->getNumRows() > 0)
        {
            $db->query("ALTER TABLE `$table` DROP FOREIGN KEY `$fk`");
        }
    }

    $db->setPrefix($current_prefix);
}
