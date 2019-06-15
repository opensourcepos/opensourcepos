<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Module class
 */

class Module extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	public function get_module_name($module_id)
	{
		$query = $this->db->get_where('modules', array('module_id' => $module_id), 1);

		if($query->num_rows() == 1)
		{
			$row = $query->row();

			return $this->lang->line($row->name_lang_key);
		}

		return $this->lang->line('error_unknown');
	}

	public function get_module_desc($module_id)
	{
		$query = $this->db->get_where('modules', array('module_id' => $module_id), 1);

		if($query->num_rows() == 1)
		{
			$row = $query->row();

			return $this->lang->line($row->desc_lang_key);
		}

		return $this->lang->line('error_unknown');
	}

	public function get_all_permissions()
	{
		$this->db->from('permissions');

		return $this->db->get();
	}

	public function get_all_subpermissions()
	{
		$this->db->from('permissions');
		$this->db->join('modules AS modules', 'modules.module_id = permissions.module_id');
		// can't quote the parameters correctly when using different operators..
		$this->db->where('modules.module_id != ', 'permission_id', FALSE);

		return $this->db->get();
	}

	public function get_all_modules()
	{
		$this->db->from('modules');
		$this->db->order_by('sort', 'asc');
		return $this->db->get();
	}

	public function get_allowed_home_modules($person_id)
	{
		$menus = array('home', 'both');
		$this->db->from('modules');
		$this->db->join('permissions', 'permissions.permission_id = modules.module_id');
		$this->db->join('grants', 'permissions.permission_id = grants.permission_id');
		$this->db->where('person_id', $person_id);
		$this->db->where_in('menu_group', $menus);
		$this->db->where('sort !=', 0);
		$this->db->order_by('sort', 'asc');
		return $this->db->get();
	}

	public function get_allowed_office_modules($person_id)
	{
		$menus = array('office', 'both');
		$this->db->from('modules');
		$this->db->join('permissions', 'permissions.permission_id = modules.module_id');
		$this->db->join('grants', 'permissions.permission_id = grants.permission_id');
		$this->db->where('person_id', $person_id);
		$this->db->where_in('menu_group', $menus);
		$this->db->where('sort !=', 0);
		$this->db->order_by('sort', 'asc');
		return $this->db->get();
	}

	/**
	 * This method is used to set the show the office navigation icon on the home page
	 * which happens when the sort value is greater than zero
	 */
	public function set_show_office_group($show_office_group)
	{
		if($show_office_group)
		{
			$sort = 999;
		}
		else
		{
			$sort = 0;
		}

		$modules_data = array(
			'sort' => $sort
		);
		$this->db->where('module_id', 'office');
		$this->db->update('modules', $modules_data);
	}

	/**
	 * This method is used to show the office navigation icon on the home page
	 * which happens when the sort value is greater than zero
	 */
	public function get_show_office_group()
	{
		$this->db->select('sort');
		$this->db->from('grants');
		$this->db->where('module_id', 'office');
		$this->db->from('modules');
		return $this->db->get()->row()->sort;
	}
}
?>
