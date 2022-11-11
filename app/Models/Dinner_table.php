<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Dinner_table class
 */

class Dinner_table extends CI_Model
{
	public function exists($dinner_table_id)
	{
		$this->db->from('dinner_tables');
		$this->db->where('dinner_table_id', $dinner_table_id);

		return ($this->db->get()->num_rows() >= 1);
	}

	public function save($table_data, $dinner_table_id)
	{
		$table_data_to_save = array('name' => $table_data['name'], 'deleted' => 0);

		if(!$this->exists($dinner_table_id))
		{
			return $this->db->insert('dinner_tables', $table_data_to_save);
		}

		$this->db->where('dinner_table_id', $dinner_table_id);

		return $this->db->update('dinner_tables', $table_data_to_save);
	}

	/**
	Get empty tables
	*/
	public function get_empty_tables($current_dinner_table_id)
	{
		$this->db->from('dinner_tables');
		$this->db->where('status', 0);
		$this->db->or_where('dinner_table_id', $current_dinner_table_id);
		$this->db->where('deleted', 0);

		$empty_tables = $this->db->get()->result_array();

		$empty_tables_array = array();
		foreach($empty_tables as $empty_table)
		{
			$empty_tables_array[$empty_table['dinner_table_id']] = $empty_table['name'];
		}

		return $empty_tables_array;
	}

	public function get_name($dinner_table_id)
	{
		if(empty($dinner_table_id))
		{
			return '';
		}
		else
		{
			$this->db->from('dinner_tables');
			$this->db->where('dinner_table_id', $dinner_table_id);

			return $this->db->get()->row()->name;
		}
	}

	public function is_occupied($dinner_table_id)
	{
		if(empty($dinner_table_id))
		{
			return FALSE;
		}
		else
		{
			$this->db->from('dinner_tables');
			$this->db->where('dinner_table_id', $dinner_table_id);

			return ($this->db->get()->row()->status == 1);
		}
	}

	public function get_all()
	{
		$this->db->from('dinner_tables');
		$this->db->where('deleted', 0);

		return $this->db->get();
	}

	/**
	Deletes one table
	*/
	public function delete($dinner_table_id)
	{
		$this->db->where('dinner_table_id', $dinner_table_id);

		return $this->db->update('dinner_tables', array('deleted' => 1));
	}

	/**
	 * Occupy table
	 * Ignore the Delivery and Takeaway "tables".  They should never be occupied.
	 */
	public function occupy($dinner_table_id)
	{
		if($dinner_table_id > 2 )
		{
			$this->db->where('dinner_table_id', $dinner_table_id);
			return $this->db->update('dinner_tables', array('status' => 1));
		}
		else
		{
			return true;
		}
	}

	/**
	Release table
	 */
	public function release($dinner_table_id)
	{
		if($dinner_table_id > 2 )
		{
			$this->db->where('dinner_table_id', $dinner_table_id);
			return $this->db->update('dinner_tables', array('status' => 0));
		}
		else
		{
			return true;
		}
	}

	/**
	Swap tables
	 */
	public function swap_tables($release_dinner_table_id, $occupy_dinner_table_id)
	{
		return $this->release($release_dinner_table_id) && $this->occupy($occupy_dinner_table_id);
	}

}
?>
