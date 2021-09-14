<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Stock_location class
 *
 * @property employee employee
 * @property item item
 * @property session session
 *
 */

class Stock_location extends Model
{
	public function __construct()
	{
		$this->employee = model('Employee');
		$this->item = model('Item');

		$this->session = session();
	}
	public function exists(int $location_id = -1): bool
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

	public function get_undeleted_all(string $module_id = 'items')
	{
		$builder = $this->db->table('stock_locations');
		$builder->join('permissions AS permissions', 'permissions.location_id = stock_locations.location_id');
		$builder->join('grants AS grants', 'grants.permission_id = permissions.permission_id');
		$builder->where('person_id', $this->session->userdata('person_id'));
		$builder->like('permissions.permission_id', $module_id, 'after');
		$builder->where('deleted', 0);

		return $builder->get();
	}

	public function show_locations(string $module_id = 'items'): bool
	{
		$stock_locations = $this->get_allowed_locations($module_id);

		return count($stock_locations) > 1;
	}

	public function multiple_locations(): bool
	{
		return $this->get_all()->getNumRows() > 1;
	}

	public function get_allowed_locations(string $module_id = 'items'): array
	{
		$stock = $this->get_undeleted_all($module_id)->getResultArray();
		$stock_locations = [];

		foreach($stock as $location_data)
		{
			$stock_locations[$location_data['location_id']] = $location_data['location_name'];
		}

		return $stock_locations;
	}

	public function is_allowed_location(int $location_id, string $module_id = 'items'): bool
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

	public function get_default_location_id(string $module_id = 'items'): int
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

	public function get_location_name(int $location_id): string
	{
		$builder = $this->db->table('stock_locations');
		$builder->where('location_id', $location_id);

		return $builder->get()->getRow()->location_name;
	}

	public function get_location_id(string $location_name): int
	{
		$builder = $this->db->table('stock_locations');
		$builder->where('location_name', $location_name);

		return $builder->get()->getRow()->location_id;
	}

	public function save(array &$location_data, int $location_id): bool
	{
		$location_name = $location_data['location_name'];

		$location_data_to_save = ['location_name' => $location_name, 'deleted' => 0];

		if(!$this->exists($location_id))
		{
			$this->db->transStart();

			$builder = $this->db->table('stock_locations');
			$builder->insert($location_data_to_save);
 			$location_id = $this->db->insertID();

			$this->_insert_new_permission('items', $location_id, $location_name);	//TODO: need to refactor out the hungarian notation.
			$this->_insert_new_permission('sales', $location_id, $location_name);
			$this->_insert_new_permission('receivings', $location_id, $location_name);

			// insert quantities for existing items
			$items = $this->item->get_all();
			foreach($items->getResultArray() as $item)
			{
				$quantity_data = ['item_id' => $item['item_id'], 'location_id' => $location_id, 'quantity' => 0];

				$builder = $this->db->table('item_quantities');
				$builder->insert($quantity_data);
			}

			$this->db->transComplete();

			return $this->db->transStatus();
		}

		$original_location_name = $this->get_location_name($location_id);

		$builder = $this->db->table('stock_locations');

		if($original_location_name != $location_name)
		{
			$builder->where('location_id', $location_id);
			$builder->delete('permissions');

			$this->_insert_new_permission('items', $location_id, $location_name);
			$this->_insert_new_permission('sales', $location_id, $location_name);
			$this->_insert_new_permission('receivings', $location_id, $location_name);
		}

		$builder->where('location_id', $location_id);

		return $builder->update($location_data_to_save);
	}

	private function _insert_new_permission(string $module, int $location_id, string $location_name)	//TODO: refactor out hungarian notation
	{
		// insert new permission for stock location
		$permission_id = $module . '_' . str_replace(' ', '_', $location_name);
		$permission_data = array('permission_id' => $permission_id, 'module_id' => $module, 'location_id' => $location_id);

		$builder = $this->db->table('permissions');
		$builder->insert($permission_data);

		// insert grants for new permission
		$employees = $this->employee->get_all();

		foreach($employees->getResultArray() as $employee)
		{
			// Retrieve the menu_group assigned to the grant for the module and use that for the new stock locations
			$menu_group = $this->employee->get_menu_group($module, $employee['person_id']);

			$grants_data = ['permission_id' => $permission_id, 'person_id' => $employee['person_id'], 'menu_group' => $menu_group];

			$builder = $this->db->table('grants');
			$builder->insert($grants_data);
		}
	}

	/*
	 Deletes one item
	 */
	public function delete(int $location_id): bool	//TODO: for these delete methods, it wants us to add a second parameter with a soft delete override... presumably for GDPR?
	{
		$this->db->transStart();

		$builder = $this->db->table('stock_locations');
		$builder->where('location_id', $location_id);
		$builder->update(['deleted' => 1]);

		$builder = $this->db->table('permissions');
		$builder->where('location_id', $location_id);
		$builder->delete();

		$this->db->transComplete();

		return $this->db->transStatus();
	}
}
?>
