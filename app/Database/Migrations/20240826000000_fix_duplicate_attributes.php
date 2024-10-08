<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\Attribute;
use CodeIgniter\Database\ResultInterface;

class fix_duplicate_attributes extends Migration
{
	/**
	 * Perform a migration step.
	 */
	public function up(): void
	{
		$rows_to_keep = $this->get_all_duplicate_attributes();
		$this->remove_duplicate_attributes($rows_to_keep);

		helper('migration');

		$this->drop_foreign_key_constraints();

		execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.4.0_attribute_links_unique_constraint.sql');
	}

	/**
	 * Retrieves from the database all rows where the item_id and definition_id are the same AND the sale_id/receiving_id is null.
	 * It also excludes null item_id rows as those are dropdown items.
	 *
	 * @return ResultInterface Results containing item_id, definition_id and attribute_id in each row.
	 */
	private function get_all_duplicate_attributes(): ResultInterface
	{
		$builder = $this->db->table('attribute_links');
		$builder->select('item_id, definition_id, MIN(attribute_id) as attribute_id');
		$builder->where('sale_id IS NULL');
		$builder->where('receiving_id IS NULL');
		$builder->where('item_id IS NOT NULL');
		$builder->groupBy('item_id, definition_id');
		$builder->having('COUNT(attribute_id) > 1');
		return $builder->get();
	}

	/**
	 * Removes the duplicate attributes from the database.
	 *
	 * @param ResultInterface $rows_to_keep A multidimensional associative array containing item_id, definition_id and attribute_id in each row which should be kept in the database.
	 * @return void
	 */
	private function remove_duplicate_attributes(ResultInterface $rows_to_keep): void
	{
		$attribute = model(Attribute::class);
		foreach($rows_to_keep->getResult() as $row)
		{
			$attribute->deleteAttributeLinks($row->item_id, $row->definition_id);	//Deletes all attribute links for the item_id/definition_id combination
			$attribute->saveAttributeLink($row->item_id, $row->definition_id, $row->attribute_id);
		}
	}

	/**
	 * Drops the foreign key constraints from the attribute_links table.
	 * This is required to successfully create the generated unique constraint.
	 *
	 * @return void
	 */
	private function drop_foreign_key_constraints(): void
	{
		$foreignKeys = [
			'ospos_attribute_links_ibfk_1',
			'ospos_attribute_links_ibfk_2',
			'ospos_attribute_links_ibfk_3',
			'ospos_attribute_links_ibfk_4',
			'ospos_attribute_links_ibfk_5'
		];

		$current_prefix = $this->db->getPrefix();
		$this->db->setPrefix('');
		$database_name = $this->db->database;

		foreach ($foreignKeys as $fk)
		{
			$builder = $this->db->table('INFORMATION_SCHEMA.TABLE_CONSTRAINTS');
			$builder->select('CONSTRAINT_NAME');
			$builder->where('TABLE_SCHEMA', $database_name);
			$builder->where('TABLE_NAME', 'ospos_attribute_links');
			$builder->where('CONSTRAINT_TYPE', 'FOREIGN KEY');
			$builder->where('CONSTRAINT_NAME', $fk);
			$query = $builder->get();

			if($query->getNumRows() > 0)
			{
				$this->db->query("ALTER TABLE `ospos_attribute_links` DROP FOREIGN KEY `$fk`");
			}
		}

		$this->db->setPrefix($current_prefix);
	}

	/**
	 * Revert a migration step.
	 */
	public function down(): void
	{

	}
}
