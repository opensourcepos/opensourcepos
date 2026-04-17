<?php

namespace App\Plugins\MailchimpPlugin\Models;

use App\Plugins\MailchimpPlugin\Entities\Subscription;
use CodeIgniter\Model;
use ReflectionException;

class SubscriptionModel extends Model
{
    protected $table = 'mailchimpplugin_subscriptions';
    protected $primaryKey = 'subscription_id';
    protected $useAutoIncrement = true;
    protected $returnType = Subscription::class;
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'customer_id',  // ospos_customers.person_id
        'mailchimp_id', // MD5 hash of the lowercase version of the list member's email address
        'status_id',    // ospos_mailchimpplugin_subscription_status.status_id
        'created_at',   // Timestamp of when the subscription was created
        'updated_at',   // Timestamp of when the subscription was last updated
        'deleted_at'    // Timestamp of when the subscription was deleted
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    public function upsert(array $customerData): int|false
    {
        try {
            if ($this->save($customerData)) {
                return $customerData[$this->primaryKey] ?? $this->getInsertID();
            }
        } catch (ReflectionException $e) {
            log_message('error', 'Subscription upsert failed: ' . $e->getMessage());
        }
        return false;
    }

    public function exists(?int $customerId): bool
    {
        if (!is_int($customerId) || $customerId < 1) {
            return false;
        }

        $builder = $this->db->table($this->table);
        $builder->where('customer_id', $customerId);

        return ($builder->get()->getNumRows() === 1);
    }
}
