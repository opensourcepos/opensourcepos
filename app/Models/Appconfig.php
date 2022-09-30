<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use ReflectionException;

/**
 * Appconfig class
 *
 * @property mixed config
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

	public function get_value(string $key, string $default = ''): string
	{
		$builder = $this->db->table('app_config');
		$query = $builder->getWhere('key', $key, 1);

		if($query->getNumRows() == 1)	//TODO: ===
		{
			return $query->getRow()->value;
		}

		return $default;
	}

	/**
	 * Calls the parent save() from BaseModel but additionally updates the cached array value.
	 * @param $data
	 * @return bool
	 * @throws ReflectionException
	 */
	public function save($data): bool
	{
		$this->config = config('OSPOS');
		$success = parent::save($data);

		$key = array_keys($data)[0];

		if($success)
		{
			$this->config[$key] = $data[$key];
		}

		return $success;
	}

	/**
	 * @throws ReflectionException
	 */
	public function batch_save(array $data): bool
	{
		$success = TRUE;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->transStart();

		foreach($data as $element)
		{
			$success &= $this->save($element);
		}

		$this->db->transComplete();

		$success &= $this->db->transStatus();

		return $success;
	}

	public function delete(string $key = null, bool $purge = false): bool
	{
		$builder = $this->db->table('app_config');
		return $builder->delete(['key' => $key]);
	}

	public function delete_all(): bool	//TODO: This function is never used in the code. Consider removing it.
	{
		$builder = $this->db->table('app_config');
		return $builder->emptyTable();
	}

	/**
	 * @throws ReflectionException
	 */
	public function acquire_save_next_invoice_sequence(): string
	{
		$last_used = (int)config('OSPOS')->last_used_invoice_number + 1;
		$this->save(['last_used_invoice_number' => $last_used]);

		return $last_used;
	}

	/**
	 * @throws ReflectionException
	 */
	public function acquire_save_next_quote_sequence(): string
	{
		$last_used = (int)config('OSPOS')->last_used_quote_number + 1;
		$this->save(['last_used_quote_number' => $last_used]);

		return $last_used;
	}

	/**
	 * @throws ReflectionException
	 */
	public function acquire_save_next_work_order_sequence(): string
	{
		$last_used = (int)config('OSPOS')->last_used_work_order_number + 1;
		$this->save(['last_used_work_order_number' => $last_used]);

		return $last_used;
	}
}