<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;

/**
 * Appconfig class
 */
class Appconfig extends Model
{
	public function exists(string $key): bool
	{
		$builder = $this->db->table('app_config');
		$builder->where('app_config.key', $key);	//TODO: I think we can skip app_config. and just write where(key, $key);

		return ($builder->get()->getNumRows() == 1);	//TODO: ===
	}

	public function get_all(): ResultInterface
	{
		$builder = $this->db->table('app_config');
		$builder->orderBy('key', 'asc');

		return $builder->get();
	}

	//TODO: need to fix this function so it either isn't overriding the basemodel function or get it in line
	public function get(string $key, string $default = ''): string
	{
		$builder = $this->db->table('app_config');
		$query = $builder->getWhere('key', $key, 1);

		if($query->getNumRows() == 1)	//TODO: ===
		{
			return $query->getRow()->value;
		}

		return $default;
	}

	public function save(string $key, string $value): bool
	{
		$config_data = ['key'   => $key, 'value' => $value];
		
		$builder = $this->db->table('app_config');
		
		if(!$this->exists($key))
		{
			return $builder->insert($config_data);
		}

		$builder->where('key', $key);

		return $builder->update($config_data);
	}

	public function batch_save(array $data): bool
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		foreach($data as $key=>$value)
		{
			$success &= $this->save($key, $value);	//TODO: Reflection Exception
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	public function delete(string $key = null, bool $purge = false): bool
	{
		$builder = $this->db->table('app_config');
		return $builder->delete('key', $key);
	}

	public function delete_all(): bool
	{
		$builder = $this->db->table('app_config');
		return $builder->emptyTable();
	}

	public function acquire_save_next_invoice_sequence(): string
	{
		$last_used = $this->get('last_used_invoice_number') + 1;	//TODO: Get returns a string... make sure that this will work properly and not need to be cast to an int first.
		$this->save('last_used_invoice_number', $last_used);	//TODO: Reflection Exception

		return $last_used;
	}

	public function acquire_save_next_quote_sequence(): string
	{
		$last_used = $this->get('last_used_quote_number') + 1;
		$this->save('last_used_quote_number', $last_used);	//TODO: Reflection Exception

		return $last_used;
	}

	public function acquire_save_next_work_order_sequence(): string
	{
		$last_used = $this->get('last_used_work_order_number') + 1;
		$this->save('last_used_work_order_number', $last_used);	//TODO: Reflection Exception

		return $last_used;
	}
}
?>
