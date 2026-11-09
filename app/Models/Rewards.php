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

    public function exists(int $rewardsId): bool
    {
        $builder = $this->db->table('sales_reward_points');
        $builder->where('id', $rewardsId);

        return $builder->get()->getNumRows() >= 1;
    }

    /**
     * Inserts or updates a rewards
     */
    public function save_value(array &$rewardsData, int|bool $rewardsId = false): bool
    {
        $builder = $this->db->table('sales_reward_points');
        if (!$rewardsId || !$this->exists((int) $rewardsId)) {
            if ($builder->insert($rewardsData)) {
                $rewardsData['id'] = $this->db->insertID();

                return true;
            }

            return false;
        }

        $builder->where('id', $rewardsId);

        return $builder->update($rewardsData);
    }
}
