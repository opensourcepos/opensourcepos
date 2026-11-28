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
    public function save_value(array &$rewardsData, bool $rewardsId = false): bool
    {
        $builder = $this->db->table('sales_reward_points');
        if (!$rewardsId || !$this->exists($rewardsId)) {    // TODO: looks like we are missing the exists function in this class
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
