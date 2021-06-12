<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_database_optimizations extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		error_log('Migrating database_optimizations');
		$CI =& get_instance();

		$CI->Attribute->delete_orphaned_values();

		$this->migrate_duplicate_attribute_values(DECIMAL);
		$this->migrate_duplicate_attribute_values(DATE);

		//Select all attributes that have data in more than one column
		$this->db->select('attribute_id, attribute_value, attribute_decimal, attribute_date');
		$this->db->group_start();
			$this->db->where('attribute_value IS NOT NULL');
			$this->db->where('attribute_date IS NOT NULL');
		$this->db->group_end();
		$this->db->or_group_start();
			$this->db->where('attribute_value IS NOT NULL');
			$this->db->where('attribute_decimal IS NOT NULL');
		$this->db->group_end();
		$attribute_values = $this->db->get('attribute_values');

		$this->db->trans_start();

		//Clean up Attribute values table where there is an attribute value and an attribute_date/attribute_decimal
		foreach($attribute_values->result_array() as $attribute_value)
		{
			$attribute_links = $this->db->query('SELECT links.definition_id, links.item_id, links.attribute_id, defs.definition_type FROM ospos_attribute_links links JOIN ospos_attribute_definitions defs ON defs.definition_id = links.definition_id where attribute_id = '. $attribute_value['attribute_id']);

			$this->db->where('attribute_id', $attribute_value['attribute_id']);
			$this->db->delete('attribute_values');

			foreach($attribute_links->result_array() as $attribute_link)
			{
				$this->db->where('attribute_id',$attribute_link['attribute_id']);
				$this->db->where('item_id',$attribute_link['item_id']);
				$this->db->delete('attribute_links');

				switch($attribute_link['definition_type'])
				{
					case DECIMAL:
						$value = $attribute_value['attribute_decimal'];
						break;
					case DATE:
						$attribute_date	= DateTime::createFromFormat('Y-m-d', $attribute_value['attribute_date']);
						$value			= $attribute_date->format($CI->Appconfig->get('dateformat'));
						break;
					default:
						$value = $attribute_value['attribute_value'];
						break;
				}

				$CI->Attribute->save_value($value, $attribute_link['definition_id'], $attribute_link['item_id'], FALSE, $attribute_link['definition_type']);
			}
		}
		$this->db->trans_complete();

		execute_script(APPPATH . 'migrations/sqlscripts/3.4.0_database_optimizations.sql');
		error_log('Migrating database_optimizations completed');
	}
	/**
	 * Given the type of attribute, deletes any duplicates it finds in the attribute_values table and reassigns those
	 */
	private function migrate_duplicate_attribute_values($attribute_type)
	{
	//Remove duplicate attribute values needed to make attribute_decimals and attribute_dates unique
		$this->db->trans_start();

		$column = 'attribute_' . strtolower($attribute_type);

		$this->db->select("$column, attribute_id");
		$this->db->group_by($column);
		$this->db->having("COUNT($column) > 1");
		$duplicated_values = $this->db->get('attribute_values');

		foreach($duplicated_values->result_array() as $duplicated_value)
		{
			$this->db->select('attribute_id');
			$this->db->where($column, $duplicated_value[$column]);
			$attribute_ids_to_fix = $this->db->get('attribute_values');

			$this->reassign_duplicate_attribute_values($attribute_ids_to_fix, $duplicated_value);
		}

		$this->db->trans_complete();
	}

	/**
	 * Updates the attribute_id in all attribute_link rows with duplicated attribute_ids then deletes unneeded rows from attribute_values
	 * @param attribute_ids_to_fix
	 * @param decimal
	 */
	private function reassign_duplicate_attribute_values($attribute_ids_to_fix, $attribute_value)
	{
		foreach($attribute_ids_to_fix->result_array() as $attribute_id)
		{
			//Update attribute_link with the attribute_id we are keeping
			$this->db->where('attribute_id', $attribute_id['attribute_id']);
			$this->db->update('attribute_links', array('attribute_id' => $attribute_value['attribute_id']));

			//Delete the row from attribute_values if it isn't our keeper
			if($attribute_id['attribute_id'] !== $attribute_value['attribute_id'])
			{
				$this->db->where('attribute_id', $attribute_id['attribute_id']);
				$this->db->delete($this->db->dbprefix('attribute_values'));
			}
		}
	}

	public function down()
	{
	}
}
?>