<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class Migration_fix_keys_for_db_upgrade extends Migration {

	private array $constraints;

	/**
	 * Perform a migration step.
	 */
	public function up(): void
	{
		$this->db->query("ALTER TABLE `ospos_tax_codes` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;");

		if (!$this->index_exists('ospos_customers', 'company_name'))
		{
			$this->db->query("ALTER TABLE `ospos_customers` ADD INDEX(`company_name`)");
		}

		$checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
		$foreignKeyExists = $this->db->query($checkSql)->getRow();

		if ($foreignKeyExists)
		{
			$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP FOREIGN KEY ospos_sales_items_taxes_ibfk_1');
		}

		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
			. ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id, item_id, line) '
			. ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id, item_id, line)');


		$this->create_primary_key('customers', 'person_id');
		$this->create_primary_key('employees', 'person_id');
		$this->create_primary_key('suppliers', 'person_id');
	}

	/**
	 * Revert a migration step.
	 */
	public function down(): void
	{
		$checkSql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->prefixTable('sales_items_taxes') . "' AND CONSTRAINT_NAME = 'ospos_sales_items_taxes_ibfk_1'";
		$foreignKeyExists = $this->db->query($checkSql)->getRow();

		if ($foreignKeyExists)
		{
			$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes') . ' DROP CONSTRAINT ospos_sales_items_taxes_ibfk_1');
		}

		$this->db->query('ALTER TABLE ' . $this->db->prefixTable('sales_items_taxes')
			. ' ADD CONSTRAINT ospos_sales_items_taxes_ibfk_1 FOREIGN KEY (sale_id) '
			. ' REFERENCES ' . $this->db->prefixTable('sales_items') . ' (sale_id)');
	}

	private function create_primary_key(string $table, string $index): void
	{
		$result = $this->db->query('SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name= \'' . $this->db->getPrefix() . "$table' AND column_key = '$index'");

		if ( ! $result->getRowArray())
		{
			$constraintsDropped = $this->delete_index($table, $index);

			$forge = Database::forge();
			$forge->addPrimaryKey($table, '');

			if($constraintsDropped)
			{
				$this->recreateConstraints($table, $index);
			}
		}
	}

	private function index_exists(string $table, string $index): bool
	{
		$result = $this->db->query('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = \'' . $this->db->getPrefix() . "$table' AND index_name = '$index'");
		$row_array = $result->getRowArray();
		return $row_array && $row_array['COUNT(*)'] > 0;
	}

	private function delete_index(string $table, string $index): bool
	{
		$constraintsDropped = false;
		if ($this->index_exists($table, $index))
		{
			$constraintsDropped = $this->dropConstraints($table, $index);
			$forge = Database::forge();
			$forge->dropKey($table, $index, FALSE);
		}

		return $constraintsDropped;
	}

	/**
	 * Checks to see if a foreign key constraint exists and drops it if it does.
	 * @param string $table The table name to check for the constraint
	 * @param string $index The index name to check for the constraint
	 * @return void
	 */
	private function dropConstraints(string $table, string $index): bool
	{
		$sql = "SELECT 
			kcu.CONSTRAINT_NAME, 
			kcu.COLUMN_NAME, 
			kcu.TABLE_NAME, 
			kcu.REFERENCED_COLUMN_NAME, 
			kcu.REFERENCED_TABLE_NAME, 
			rc.UPDATE_RULE, 
			rc.DELETE_RULE
			FROM information_schema.KEY_COLUMN_USAGE kcu
			JOIN information_schema.REFERENTIAL_CONSTRAINTS rc 
			ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME 
			WHERE kcu.TABLE_SCHEMA = DATABASE() 
			AND (kcu.REFERENCED_TABLE_NAME = '" . $this->db->getPrefix() . "$table' 
			AND kcu.REFERENCED_COLUMN_NAME = '$index' 
			OR kcu.TABLE_NAME = '" . $this->db->getPrefix() . "$table' 
			AND kcu.COLUMN_NAME = '$index')
		";
		$query = $this->db->query($sql);
		$this->constraints = $query->getResult();

		$constraintsDropped = false;
		foreach($this->constraints as $constraint)
		{
			$constraintName = $constraint->CONSTRAINT_NAME;
			$referencingTable = str_replace($this->db->getPrefix(), '', $constraint->TABLE_NAME);

			$forge = Database::forge();
			if($forge->dropForeignKey($referencingTable, $constraintName))
			{
				$constraintsDropped = true;
			}
		}

		return $constraintsDropped;
	}

	/**
	 * Re-creates the missing foreign key constraint which needed to be dropped in order to add the Primary Key
	 * @return void
	 */
	private function recreateConstraints(): void
	{
		$forge = Database::forge();
		foreach($this->constraints as $constraint)
		{
			$index = $constraint->COLUMN_NAME;
			$table = str_replace($this->db->getPrefix(), '', $constraint->TABLE_NAME);
			$referencedTable = $constraint->REFERENCED_TABLE_NAME;
			$constraintName = $constraint->CONSTRAINT_NAME;
			$onUpdate = $constraint->UPDATE_RULE;
			$onDelete = $constraint->DELETE_RULE;

			$forge->addForeignKey($index, $referencedTable, $index, $onUpdate, $onDelete, $constraintName);
			$forge->processIndexes($table);
		}
	}
}
