<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use ReflectionException;

/**
 * Appconfig class
 *
 *
 */
class Appconfig extends Model
{
	protected $table = 'app_config';
	protected $primaryKey = 'key';
	protected $useAutoIncrement = false;
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'key',
		'value'
	];

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
		$query = $builder->getWhere(['key' => $key], 1);

		if($query->getNumRows() == 1)	//TODO: ===
		{
			return $query->getRow()->value;
		}

		return $default;
	}

	/**
	 * Calls the parent save() from BaseModel and updates the cached reference.
	 * @param array|object $data
	 * @return bool
	 * @throws ReflectionException
	 */
	public function save($data): bool	//TODO: This is puking: Allowed fields must be specified for model: "App\Models\Appconfig"
	{
		$success = parent::save($data);
		$config = config('OSPOS');

		if($success)
		{
			$config->update_settings();	//TODO: We need to investigate whether there is a possibility of stale data. It updates the cache in this function, but when save() returns any instances of $config->settings[] may not be updated yet.
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

	public function delete($id = null, bool $purge = false)
	{
		$builder = $this->db->table('app_config');
		return $builder->delete(['key' => $id]);
	}

	public function delete_all(): bool	//TODO: This function is never used in the code. Consider removing it.
	{
		$builder = $this->db->table('app_config');
		return $builder->emptyTable();
	}

	/**
	 * @throws ReflectionException
	 */
	public function acquire_next_invoice_sequence(bool $save = true): string
	{
		$config = config('OSPOS')->settings;
		$last_used = (int)$config['last_used_invoice_number'] + 1;

		if($save)
		{
			$this->save(['last_used_invoice_number'=> $last_used]);
		}

		return $last_used;
	}

	/**
	 * @throws ReflectionException
	 */
	public function acquire_next_quote_sequence(bool $save = true): string
	{
		$config = config('OSPOS')->settings;
		$last_used = (int)$config['last_used_quote_number'] + 1;

		if($save)
		{
			$this->save(['last_used_quote_number' => $last_used]);
		}

		return $last_used;
	}

	/**
	 * @throws ReflectionException
	 */
	public function acquire_next_work_order_sequence(bool $save = true): string
	{
		$config = config('OSPOS')->settings;
		$last_used = (int)$config['last_used_work_order_number'] + 1;

		if($save)
		{
			$this->save(['last_used_work_order_number' => $last_used]);
		}

		return $last_used;
	}
}
