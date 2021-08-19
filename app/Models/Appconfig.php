<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Appconfig class
 */

class Appconfig extends Model
{
	public function exists($key)
	{
		$builder = $this->db->table('app_config');
		$builder->where('app_config.key', $key);

		return ($builder->get()->getNumRows() == 1);
	}

	public function get_all()
	{
		$builder = $this->db->table('app_config');
		$builder->orderBy('key', 'asc');

		return $builder->get();
	}
//TODO: need to fix this function so it either isn't overriding the basemodel function or get it in line
	public function get($key, $default = '')
	{
		$builder = $this->db->table('app_config');
		$query = $builder->getWhere('key', $key, 1);

		if($query->getNumRows() == 1)
		{
			return $query->getRow()->value;
		}

		return $default;
	}

	public function save($key, $value): bool
	{
		$config_data = [
			'key'   => $key,
			'value' => $value
		];
		
		$builder = $this->db->table('app_config');
		
		if(!$this->exists($key))
		{
			return $builder->insert($config_data);
		}

		$builder->where('key', $key);

		return $builder->update($config_data);
	}

	public function batch_save($data)
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		foreach($data as $key=>$value)
		{
			$success &= $this->save($key, $value);
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}
//TODO: need to fix this function so it either isn't overriding the basemodel function or get it in line
	public function delete($key): bool
	{
		$builder = $this->db->table('app_config');
		return $builder->delete('key', $key);
	}

	public function delete_all()
	{
		$builder = $this->db->table('app_config');
		return $builder->emptyTable();
	}

	public function acquire_save_next_invoice_sequence()
	{
		$last_used = $this->get('last_used_invoice_number') + 1;
		$this->save('last_used_invoice_number', $last_used);
		return $last_used;
	}

	public function acquire_save_next_quote_sequence()
	{
		$last_used = $this->get('last_used_quote_number') + 1;
		$this->save('last_used_quote_number', $last_used);
		return $last_used;
	}

	public function acquire_save_next_work_order_sequence()
	{
		$last_used = $this->get('last_used_work_order_number') + 1;
		$this->save('last_used_work_order_number', $last_used);
		return $last_used;
	}
}
?>
