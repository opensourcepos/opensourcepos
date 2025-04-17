<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class Migration_fix_keys_for_db_upgrade extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $this->db->query("ALTER TABLE `ospos_tax_codes` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;");

        if (!$this->indexExists('ospos_customers', 'company_name')) {
            $this->db->query("ALTER TABLE `ospos_customers` ADD INDEX(`company_name`)");
        }

        $checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
        $foreignKeyExists = $this->db->query($checkSql)->getRow();

        if ($foreignKeyExists) {
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP FOREIGN KEY ospos_sales_items_taxes_ibfk_1');
        }

        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
            . ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id, item_id, line) '
            . ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id, item_id, line)');


        $this->createPrimaryKey('customers', 'person_id');
        $this->createPrimaryKey('employees', 'person_id');
        $this->createPrimaryKey('suppliers', 'person_id');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
        $checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
        $foreignKeyExists = $this->db->query($checkSql)->getRow();

        if ($foreignKeyExists) {
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP CONSTRAINT ospos_sales_items_taxes_ibfk_1');
        }

        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
            . ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id) '
            . ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id)');
    }

    private function createPrimaryKey(string $table, string $index): void
    {
        $result = $this->db->query('SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name= \'' . $this->db->getPrefix() . "$table' AND column_key = '$index'");

        if (! $result->getRowArray()) {
            $constraints = $this->deleteForeignKeyConstraints($table, $index);
            $this->deleteIndex($table, $index);
            $forge = Database::forge();
            $forge->addPrimaryKey($index,'PRIMARY');
            $forge->processIndexes($table);
            $this->recreateForeignKeyConstraints($constraints);
        }
    }

    private function deleteForeignKeyConstraints(string $table, string $column): array
    {
        $result = $this->db->query("
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
                AND ((kcu.REFERENCED_TABLE_NAME = '" . $this->db->getPrefix() . "$table' AND kcu.REFERENCED_COLUMN_NAME = '$column')
                OR (kcu.TABLE_NAME = '" . $this->db->getPrefix() . "$table' AND kcu.COLUMN_NAME = '$column'))
        ");

        $deletedConstraints = [];

        foreach ($result->getResultArray() as $constraint) {
            $deletedConstraints[] = [
                'constraintName' => $constraint['CONSTRAINT_NAME'],
                'tableName' => str_starts_with($constraint['TABLE_NAME'], $this->db->DBPrefix)
                    ? substr($constraint['TABLE_NAME'], strlen($this->db->DBPrefix))
                    : $constraint['TABLE_NAME'],
                'columnName' => $constraint['COLUMN_NAME'],
                'referencedTable' => str_starts_with($constraint['REFERENCED_TABLE_NAME'], $this->db->DBPrefix)
                    ? substr($constraint['REFERENCED_TABLE_NAME'], strlen($this->db->DBPrefix))
                    : $constraint['REFERENCED_TABLE_NAME'],
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

    private function deleteIndex(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            $forge = Database::forge();
            $forge->dropKey($table, $index, FALSE);
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $result = $this->db->query('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = \'' . $this->db->getPrefix() . "$table' AND index_name = '$index'");
        $row_array = $result->getRowArray();
        return $row_array && $row_array['COUNT(*)'] > 0;
    }

    private function recreateForeignKeyConstraints(array $constraints): void
    {
        if ($constraints) {
            $forge = Database::forge();
            foreach ($constraints as $constraint) {
                $forge->addForeignKey($constraint['columnName'], $constraint['referencedTable'], $constraint['referencedColumn'], $constraint['onUpdate'], $constraint['onDelete'], $constraint['constraintName']);
                $forge->processIndexes($constraint['tableName']);
            }
        }
    }
}
