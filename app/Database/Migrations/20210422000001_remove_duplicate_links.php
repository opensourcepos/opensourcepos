<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_remove_duplicate_links extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		error_log('Migrating remove_duplicate_links');


		$this->migrate_duplicate_attribute_links();

		error_log('Migrating remove_duplicate_links completed');
	}
	/**
	 * Given the type of attribute, deletes any duplicates it finds in the attribute_values table and reassigns those
	 */
	private function migrate_duplicate_attribute_links()
	{
		$CI =& get_instance();
	//Remove duplicate attribute links
		$this->db->trans_start();

		$this->db->where('sale_id', NULL);
		$this->db->where('receiving_id', NULL);
		$this->db->group_by('item_id');
		$this->db->group_by('definition_id');
		$this->db->group_by('attribute_id');
		$this->db->having('COUNT(item_id) > 1');
		$this->db->having('COUNT(definition_id) > 1');
		$this->db->having('COUNT(attribute_id) > 1');
		$duplicated_links = $this->db->get('attribute_links');

		foreach($duplicated_links->result_array() as $duplicated_link)
		{
			$this->db->where('sale_id', NULL);
			$this->db->where('receiving_id', NULL);
			$this->db->where('item_id', $duplicated_link['item_id']);
			$this->db->where('definition_id', $duplicated_link['definition_id']);
			$this->db->delete('attribute_links');

			$CI->Attribute->save_link($duplicated_link['item_id'], $duplicated_link['definition_id'], $duplicated_link['attribute_id']);
		}

		$this->db->trans_complete();
	}

	public function down()
	{
	}
}
?>