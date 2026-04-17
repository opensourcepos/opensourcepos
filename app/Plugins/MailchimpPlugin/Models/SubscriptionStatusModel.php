<?php

namespace App\Plugins\MailchimpPlugin\Models;


use CodeIgniter\Model;

class SubscriptionStatusModel extends Model
{
    protected $table = 'mailchimpplugin_subscription_status';
    protected $primaryKey = 'status_id';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = false;
    protected $returnType = 'array';
    protected $allowedFields = [
      'status_name'
    ];

    public function getStatusIdByName(string $name): ?int
    {
        $row = $this->where('status_name', $name)->first();
        return $row['status_id'] ?? null;
    }

    public function getStatusNameById(int $id): ?string
    {
        $row = $this->find($id);
        return $row['status_name'] ?? null;
    }
}
