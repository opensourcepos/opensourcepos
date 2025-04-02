<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Rewards class
 */

class Rewards extends Model    // TODO: This class is named with plural while the general practice is to name models singular
{
    protected $table = 'sales_reward_points';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'sale_id',
        'earned',
        'used'
    ];

    /**
     * Inserts or updates a rewards
     */
    public function save_value(array &$rewards_data, bool $rewards_id = false): bool
    {
        $builder = $this->db->table('sales_reward_points');
        if (!$rewards_id || !$this->exists($rewards_id)) {    // TODO: looks like we are missing the exists function in this class
            if ($builder->insert($rewards_data)) {
                $rewards_data['id'] = $this->db->insertID();

                return true;
            }

            return false;
        }

        $builder->where('id', $rewards_id);

        return $builder->update($rewards_data);
    }
}
