<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Stock_location class
 */

class Stock_location extends Model
{
	public function exists($location_id = -1)
	{
		$builder = $this->db->table('stock_locations');
		$builder->where('location_id', $location_id);

		return ($builder->get()->getNumRows() >= 1);
	}

	public function get_all()
	{
		$builder = $this->db->table('stock_locations');
		$builder->where('deleted', 0);

		return $builder->get();
	}

	public function get_undeleted_all($module_id = 'items')
	{
		$builder = $this->db->table('stock_locations');
		$builder->join('permissions AS permissions', 'permissions.location_id = stock_locations.location_id');
		$builder->join('grants AS grants', 'grants.permission_id = permissions.permission_id');
		$builder->where('person_id', $this->session->userdata('person_id'));
		$builder->like('permissions.permission_id', $module_id, 'after');
		$builder->where('deleted', 0);

		return $builder->get();
	}

	public function show_locations($module_id = 'items')
	{
		$stock_locations = $this->get_allowed_locations($module_id);

		return count($stock_locations) > 1;
	}

	public function multiple_locations()
	{
		return $this->get_all()->getNumRows() > 1;
	}

	public function get_allowed_locations($module_id = 'items')
	{
		$stock = $this->get_undeleted_all($module_id)->getResultArray();
		$stock_locations = array();
		foreach($stock as $location_data)
		{
			$stock_locations[$location_data['location_id']] = $location_data['location_name'];
		}

		return $stock_locations;
	}

	public function is_allowed_location($location_id, $module_id = 'items')
	{
		$builder = $this->db->table('stock_locations');
		$builder->join('permissions AS permissions', 'permissions.location_id = stock_locations.location_id');
		$builder->join('grants AS grants', 'grants.permission_id = permissions.permission_id');
		$builder->where('person_id', $this->session->userdata('person_id'));
		$builder->like('permissions.permission_id', $module_id, 'after');
		$builder->where('stock_locations.location_id', $location_id);
		$builder->where('deleted', 0);

		return ($builder->get()->getNumRows() == 1);
	}

	public function get_default_location_id($module_id = 'items')
	{
		$builder = $this->db->table('stock_locations');
		$builder->join('permissions AS permissions', 'permissions.location_id = stock_locations.location_id');
		$builder->join('grants AS grants', 'grants.permission_id = permissions.permission_id');
		$builder->where('person_id', $this->session->userdata('person_id'));
		$builder->like('permissions.permission_id', $module_id, 'after');
		$builder->where('deleted', 0);
		$builder->limit(1);

		return $builder->get()->getRow()->location_id;
	}

	public function get_location_name($location_id)
	{
		$builder = $this->db->table('stock_locations');
		$builder->where('location_id', $location_id);

		return $builder->get()->getRow()->location_name;
	}

	public function get_location_id($location_name)
	{
		$builder = $this->db->table('stock_locations');
		$builder->where('location_name', $location_name);

		return $builder->get()->getRow()->location_id;
	}

	public function save(&$location_data, $location_id)
	{
		$location_name = $location_data['location_name'];

		$location_data_to_save = array('location_name' => $location_name, 'deleted' => 0);

		if(!$this->exists($location_id))
		{
			$this->db->transStart();

			$builder->insert('stock_locations', $location_data_to_save);
 			$location_id = $this->db->insertID();

			$this->_insert_new_permission('items', $location_id, $location_name);
			$this->_insert_new_permission('sales', $location_id, $location_name);
			$this->_insert_new_permission('receivings', $location_id, $location_name);

			// insert quantities for existing items
			$items = $this->Item->get_all();
			foreach($items->getResultArray() as $item)
			{
				$quantity_data = array('item_id' => $item['item_id'], 'location_id' => $location_id, 'quantity' => 0);
				$builder->insert('item_quantities', $quantity_data);
			}

			$this->db->transComplete();

			return $this->db->transStatus();
		}

		$original_location_name = $this->get_location_name($location_id);

		if($original_location_name != $location_name)
		{
			$builder->where('location_id', $location_id);
			$builder->delete('permissions');

			$this->_insert_new_permission('items', $location_id, $location_name);
			$this->_insert_new_permission('sales', $location_id, $location_name);
			$this->_insert_new_permission('receivings', $location_id, $location_name);
		}

		$builder->where('location_id', $location_id);

		return $builder->update('stock_locations', $location_data_to_save);
	}

	private function _insert_new_permission($module, $location_id, $location_name)
	{
		// insert new permission for stock location
		$permission_id = $module . '_' . str_replace(' ', '_', $location_name);
		$permission_data = array('permission_id' => $permission_id, 'module_id' => $module, 'location_id' => $location_id);
		$builder->insert('permissions', $permission_data);

		// insert grants for new permission
		$employees = $this->Employee->get_all();
		foreach($employees->getResultArray() as $employee)
		{
			// Retrieve the menu_group assigned to the grant for the module and use that for the new stock locations
			$menu_group = $this->Employee->get_menu_group($module, $employee['person_id']);

			$grants_data = array('permission_id' => $permission_id, 'person_id' => $employee['person_id'], 'menu_group' => $menu_group);
			$builder->insert('grants', $grants_data);
		}
	}

	/*
	 Deletes one item
	 */
	public function delete($location_id)
	{
		$this->db->transStart();

		$builder->where('location_id', $location_id);
		$builder->update('stock_locations', array('deleted' => 1));

		$builder->where('location_id', $location_id);
		$builder->delete('permissions');

		$this->db->transComplete();

		return $this->db->transStatus();
	}
}
?>
