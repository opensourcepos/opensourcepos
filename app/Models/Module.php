<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Module class
 */
class Module extends Model
{
	function __construct()
	{
		parent::__construct();
	}

	public function get_module_name(string $module_id): string
	{
		$builder = $this->db->table('modules');
		$query = $builder->getWhere(['module_id' => $module_id], 1);

		if($query->getNumRows() == 1)	//TODO: ===
		{
			$row = $query->getRow();

			return lang($row->name_lang_key);
		}

		return lang('Error.unknown');
	}

	public function get_module_desc(string $module_id): string	//TODO: This method doesn't seem to be called in the code.  Is it needed?  Also, probably should change the name to get_module_description()
	{
		$builder = $this->db->table('modules');
		$query = $builder->getWhere(['module_id' => $module_id], 1);

		if($query->getNumRows() == 1)	//TODO: ===
		{
			$row = $query->getRow();

			return lang($row->desc_lang_key);
		}

		return lang('Error.unknown');
	}

	public function get_all_permissions()
	{
		$builder = $this->db->table('permissions');

		return $builder->get();
	}

	public function get_all_subpermissions()
	{
		$builder = $this->db->table('permissions');
		$builder->join('modules AS modules', 'modules.module_id = permissions.module_id');	//TODO: can the table parameter just be modules instead of modules AS modules?

		// can't quote the parameters correctly when using different operators..
		$builder->where('modules.module_id != ', 'permission_id', FALSE);

		return $builder->get();
	}

	public function get_all_modules()
	{
		$builder = $this->db->table('modules');
		$builder->orderBy('sort', 'asc');

		return $builder->get();
	}

	public function get_allowed_home_modules(int $person_id)
	{
		$menus = ['home', 'both'];
		$builder = $this->db->table('modules');	//TODO: this is duplicated with the code below... probably refactor a method and just pass through whether home/office modules are needed.
		$builder->join('permissions', 'permissions.permission_id = modules.module_id');
		$builder->join('grants', 'permissions.permission_id = grants.permission_id');
		$builder->where('person_id', $person_id);
		$builder->whereIn('menu_group', $menus);
		$builder->where('sort !=', 0);
		$builder->orderBy('sort', 'asc');

		return $builder->get();
	}

	public function get_allowed_office_modules(int $person_id)
	{
		$menus = ['office', 'both'];
		$builder = $this->db->table('modules');	//TODO: Duplicated code
		$builder->join('permissions', 'permissions.permission_id = modules.module_id');
		$builder->join('grants', 'permissions.permission_id = grants.permission_id');
		$builder->where('person_id', $person_id);
		$builder->whereIn('menu_group', $menus);
		$builder->where('sort !=', 0);
		$builder->orderBy('sort', 'asc');

		return $builder->get();
	}

	/**
	 * This method is used to set the show the office navigation icon on the home page
	 * which happens when the sort value is greater than zero
	 */
	public function set_show_office_group(bool $show_office_group)	//TODO: Should we return the value of update() as a bool for consistency?
	{
		if($show_office_group)	//TODO: This should be replaced with ternary notation
		{
			$sort = 999;
		}
		else
		{
			$sort = 0;
		}

		$modules_data = ['sort' => $sort];

		$builder = $this->db->table('modules');
		$builder->where('module_id', 'office');
		$builder->update($modules_data);
	}

	/**
	 * This method is used to show the office navigation icon on the home page
	 * which happens when the sort value is greater than zero
	 */
	public function get_show_office_group()
	{
		$builder = $this->db->table('grants');
		$builder->select('sort');
		$builder->where('module_id', 'office');
		$builder = $this->db->table('modules');		//TODO: this needs to be sorted out.  In the original code this was a second call to $this->db->from() without it being a second query.  I'm not sure what the original query was producing but this doesn't look right.

		return $builder->get()->getRow()->sort;
	}
}
?>
