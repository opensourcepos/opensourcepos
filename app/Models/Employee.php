<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Employee class
 */

class Employee extends Person
{
	/*
	Determines if a given person_id is an employee
	*/
	public function exists($person_id)
	{
		$builder = $this->db->table('employees');
		$builder->join('people', 'people.person_id = employees.person_id');
		$builder->where('employees.person_id', $person_id);

		return ($builder->get()->getNumRows() == 1);
	}

	public function username_exists($employee_id, $username)
	{
		$builder = $this->db->table('employees');
		$builder->where('employees.username', $username);
		$builder->where('employees.person_id <>', $employee_id);

		return ($builder->get()->getNumRows() == 1);
	}

	/*
	Gets total of rows
	*/
	public function get_total_rows()
	{
		$builder = $this->db->table('employees');
		$builder->where('deleted', 0);

		return $builder->countAllResults();
	}

	/*
	Returns all the employees
	*/
	public function get_all($limit = 10000, $offset = 0)
	{
		$builder = $this->db->table('employees');
		$builder->where('deleted', 0);
		$builder->join('people', 'employees.person_id = people.person_id');
		$builder->orderBy('last_name', 'asc');
		$builder->limit($limit);
		$this->db->offset($offset);

		return $builder->get();
	}

	/*
	Gets information about a particular employee
	*/
	public function get_info($employee_id)
	{
		$builder = $this->db->table('employees');
		$builder->join('people', 'people.person_id = employees.person_id');
		$builder->where('employees.person_id', $employee_id);
		$query = $builder->get();

		if($query->getNumRows() == 1)
		{
			return $query->getRow();
		}
		else
		{
			//Get empty base parent object, as $employee_id is NOT an employee
			$person_obj = parent::get_info(-1);

			//Get all the fields from employee table
			//append those fields to base parent object, we we have a complete empty object
			foreach($this->db->list_fields('employees') as $field)
			{
				$person_obj->$field = '';
			}

			return $person_obj;
		}
	}

	/*
	Gets information about multiple employees
	*/
	public function get_multiple_info($employee_ids)
	{
		$builder = $this->db->table('employees');
		$builder->join('people', 'people.person_id = employees.person_id');
		$builder->whereIn('employees.person_id', $employee_ids);
		$builder->orderBy('last_name', 'asc');

		return $builder->get();
	}

	/*
	Inserts or updates an employee
	*/
	public function save_employee(&$person_data, &$employee_data, &$grants_data, $employee_id = FALSE)
	{
		$success = FALSE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		if(ENVIRONMENT != 'testing' && parent::save($person_data, $employee_id))
		{
			if(!$employee_id || !$this->exists($employee_id))
			{
				$employee_data['person_id'] = $employee_id = $person_data['person_id'];
				$success = $builder->insert('employees', $employee_data);
			}
			else
			{
				$builder->where('person_id', $employee_id);
				$success = $builder->update('employees', $employee_data);
			}

			//We have either inserted or updated a new employee, now lets set permissions.
			if($success)
			{
				//First lets clear out any grants the employee currently has.
				$success = $builder->delete('grants', array('person_id' => $employee_id));

				//Now insert the new grants
				if($success)
				{
					foreach($grants_data as $grant)
					{
						$success = $builder->insert('grants', array('permission_id' => $grant['permission_id'], 'person_id' => $employee_id, 'menu_group' => $grant['menu_group']));
					}
				}
			}
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	/*
	Deletes one employee
	*/
	public function delete($employee_id)
	{
		$success = FALSE;

		//Don't let employees delete theirself
		if($employee_id == $this->get_logged_in_employee_info()->person_id)
		{
			return FALSE;
		}

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		//Delete permissions
		if($builder->delete('grants', array('person_id' => $employee_id)))
		{
			$builder->where('person_id', $employee_id);
			$success = $builder->update('employees', array('deleted' => 1));
		}

		$this->db->transComplete();

		return $success;
	}

	/*
	Deletes a list of employees
	*/
	public function delete_list($employee_ids)
	{
		$success = FALSE;

		//Don't let employees delete theirself
		if(in_array($this->get_logged_in_employee_info()->person_id, $employee_ids))
		{
			return FALSE;
		}

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		$builder->whereIn('person_id', $employee_ids);
		//Delete permissions
		if($builder->delete('grants'))
		{
			//delete from employee table
			$builder->whereIn('person_id', $employee_ids);
			$success = $builder->update('employees', array('deleted' => 1));
		}

		$this->db->transComplete();

		return $success;
 	}

	/*
	Get search suggestions to find employees
	*/
	public function get_search_suggestions($search, $include_deleted = FALSE, $limit = 5)
	{
		$suggestions = array();

		$builder = $this->db->table('employees');
		$builder->join('people', 'employees.person_id = people.person_id');
		$builder->groupStart();
			$builder->like('first_name', $search);
			$builder->orLike('last_name', $search);
			$builder->orLike('CONCAT(first_name, " ", last_name)', $search);
		$builder->groupEnd();
		if($include_deleted == FALSE)
		{
			$builder->where('deleted', 0);
		}
		$builder->orderBy('last_name', 'asc');
		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->first_name.' '.$row->last_name);
		}

		$builder = $this->db->table('employees');
		$builder->join('people', 'employees.person_id = people.person_id');
		if($include_deleted == FALSE)
		{
			$builder->where('deleted', 0);
		}
		$builder->like('email', $search);
		$builder->orderBy('email', 'asc');
		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->email);
		}

		$builder = $this->db->table('employees');
		$builder->join('people', 'employees.person_id = people.person_id');
		if($include_deleted == FALSE)
		{
			$builder->where('deleted', 0);
		}
		$builder->like('username', $search);
		$builder->orderBy('username', 'asc');
		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->username);
		}

		$builder = $this->db->table('employees');
		$builder->join('people', 'employees.person_id = people.person_id');
		if($include_deleted == FALSE)
		{
			$builder->where('deleted', 0);
		}
		$builder->like('phone_number', $search);
		$builder->orderBy('phone_number', 'asc');
		foreach($builder->get()->getResult() as $row)
		{
			$suggestions[] = array('value' => $row->person_id, 'label' => $row->phone_number);
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		return $suggestions;
	}

 	/*
	Gets rows
	*/
	public function get_found_rows($search)
	{
		return $this->search($search, 0, 0, 'last_name', 'asc', TRUE);
	}

	/*
	Performs a search on employees
	*/
	public function search($search, $rows = 0, $limit_from = 0, $sort = 'last_name', $order = 'asc', $count_only = FALSE)
	{
		// get_found_rows case
		if($count_only == TRUE)
		{
			$builder->select('COUNT(employees.person_id) as count');
		}

		$builder = $this->db->table('employees AS employees');
		$builder->join('people', 'employees.person_id = people.person_id');
		$builder->groupStart();
			$builder->like('first_name', $search);
			$builder->orLike('last_name', $search);
			$builder->orLike('email', $search);
			$builder->orLike('phone_number', $search);
			$builder->orLike('username', $search);
			$builder->orLike('CONCAT(first_name, " ", last_name)', $search);
		$builder->groupEnd();
		$builder->where('deleted', 0);

		// get_found_rows case
		if($count_only == TRUE)
		{
			return $builder->get()->getRow()->count;
		}

		$builder->orderBy($sort, $order);

		if($rows > 0)
		{
			$builder->limit($rows, $limit_from);
		}

		return $builder->get();
	}

	/*
	Attempts to login employee and set session. Returns boolean based on outcome.
	*/
	public function login($username, $password)
	{
		$query = $builder->getWhere('employees', array('username' => $username, 'deleted' => 0), 1);

		if($query->getNumRows() == 1)
		{
			$row = $query->getRow();

			// compare passwords depending on the hash version
			if($row->hash_version == 1 && $row->password == md5($password))
			{
				$builder->where('person_id', $row->person_id);
				$this->session->set_userdata('person_id', $row->person_id);
				$password_hash = password_hash($password, PASSWORD_DEFAULT);

				return $builder->update('employees', array('hash_version' => 2, 'password' => $password_hash));
			}
			elseif($row->hash_version == 2 && password_verify($password, $row->password))
			{
				$this->session->set_userdata('person_id', $row->person_id);

				return TRUE;
			}

		}

		return FALSE;
	}

	/*
	Logs out a user by destorying all session data and redirect to login
	*/
	public function logout()
	{
		$this->session->sess_destroy();

		redirect('login');
	}

	/*
	Determins if a employee is logged in
	*/
	public function is_logged_in()
	{
		return ($this->session->userdata('person_id') != FALSE);
	}

	/*
	Gets information about the currently logged in employee.
	*/
	public function get_logged_in_employee_info()
	{
		if($this->is_logged_in())
		{
			return $this->get_info($this->session->userdata('person_id'));
		}

		return FALSE;
	}

	/*
	Determines whether the employee has access to at least one submodule
	*/
	public function has_module_grant($permission_id, $person_id)
	{
		$builder = $this->db->table('grants');
		$builder->like('permission_id', $permission_id, 'after');
		$builder->where('person_id', $person_id);
		$result_count = $builder->get()->getNumRows();

		if($result_count != 1)
		{
			return ($result_count != 0);
		}

		return $this->has_subpermissions($permission_id);
	}

 	/*
	Checks permissions
	*/
	public function has_subpermissions($permission_id)
	{
		$builder = $this->db->table('permissions');
		$builder->like('permission_id', $permission_id.'_', 'after');

		return ($builder->get()->getNumRows() == 0);
	}

	/**
	 * Determines whether the employee specified employee has access the specific module.
	 */
	public function has_grant($permission_id, $person_id)
	{
		//if no module_id is null, allow access
		if($permission_id == NULL)
		{
			return TRUE;
		}

		$query = $builder->getWhere('grants', array('person_id' => $person_id, 'permission_id' => $permission_id), 1);

		return ($query->getNumRows() == 1);
	}

	/**
	 * Returns the menu group designation that this module is to appear in
	 */
	public function get_menu_group($permission_id, $person_id)
	{
		$builder->select('menu_group');
		$builder = $this->db->table('grants');
		$builder->where('permission_id', $permission_id);
		$builder->where('person_id', $person_id);

		$row = $builder->get()->getRow();

		// If no grants are assigned yet then set the default to 'home'
		if($row == NULL)
		{
			return 'home';
		}
		else
		{
			return $row->menu_group;
		}
	}

	/*
	Gets employee permission grants
	*/
	public function get_employee_grants($person_id)
	{
		$builder = $this->db->table('grants');
		$builder->where('person_id', $person_id);

		return $builder->get()->getResultArray();
	}

	/*
	Attempts to login employee and set session. Returns boolean based on outcome.
	*/
	public function check_password($username, $password)
	{
		$query = $builder->getWhere('employees', array('username' => $username, 'deleted' => 0), 1);

		if($query->getNumRows() == 1)
		{
			$row = $query->getRow();

			// compare passwords
			if(password_verify($password, $row->password))
			{
				return TRUE;
			}

		}

		return FALSE;
	}

	/*
	Change password for the employee
	*/
	public function change_password($employee_data, $employee_id = FALSE)
	{
		$success = FALSE;

		if(ENVIRONMENT != 'testing')
		{
			//Run these queries as a transaction, we want to make sure we do all or nothing
			$this->db->transStart();

			$builder->where('person_id', $employee_id);
			$success = $builder->update('employees', $employee_data);

			$this->db->transComplete();

			$success &= $this->db->transStatus();
		}

		return $success;
	}
}
?>
