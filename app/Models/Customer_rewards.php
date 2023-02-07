<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;

/**
 * Customer_rewards class
 */
class Customer_rewards extends Model
{
	protected $table = 'customer_packages';
	protected $primaryKey = 'package_id';
	protected $useAutoIncrement = true;
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'package_name',
		'points_percent',
		'deleted'
	];

	public function exists(int $package_id): bool
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return ($builder->get()->getNumRows() >= 1);
	}

	public function save_value(array $package_data, int $package_id): bool
	{
		$package_data_to_save = [
			'package_name' => $package_data['package_name'],
			'deleted' => 0,
			'points_percent' => $package_data['points_percent']
		];

		if(!$this->exists($package_id))
		{
			$builder = $this->db->table('customers_packages');
			return $builder->insert($package_data_to_save);
		}

		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->update($package_data_to_save);
	}

	public function get_name(int $package_id): string
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->get()->getRow()->package_name;
	}

	public function get_points_percent(int $package_id): float
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->get()->getRow()->points_percent;
	}

	public function get_all(): ResultInterface
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('deleted', 0);

		return $builder->get();
	}

	/**
	* Deletes one reward package
	*/
	public function delete($package_id = null, bool $purge = false): bool
	{
		$builder = $this->db->table('customers_packages');
		$builder->where('package_id', $package_id);

		return $builder->update(['deleted' => 1]);
	}
}
