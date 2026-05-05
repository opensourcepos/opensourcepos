<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'provider_id',
        'sale_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'metadata',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public const STATUS_PENDING = 'pending';
    public const STATUS_AUTHORIZED = 'authorized';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CANCELLED = 'cancelled';

    public function getTransaction(string $transactionId, ?string $providerId = null): ?array
    {
        $builder = $this->builder();
        $builder->where('transaction_id', $transactionId);
        
        if ($providerId !== null) {
            $builder->where('provider_id', $providerId);
        }
        
        $result = $builder->get()->getRowArray();
        
        return $result;
    }

    public function getTransactionsBySale(int $saleId): array
    {
        return $this->where('sale_id', $saleId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getPendingTransactions(?string $providerId = null): array
    {
        $builder = $this->builder();
        $builder->where('status', self::STATUS_PENDING);
        
        if ($providerId !== null) {
            $builder->where('provider_id', $providerId);
        }
        
        return $builder->get()->getResultArray();
    }

    public function updateStatus(int $id, string $status, array $additionalData = []): bool
    {
        $data = ['status' => $status];
        
        if (!empty($additionalData['metadata'])) {
            $existing = $this->find($id);
            if ($existing) {
                $existingMetadata = json_decode($existing['metadata'] ?? '{}', true);
                $data['metadata'] = json_encode(array_merge($existingMetadata, $additionalData['metadata']));
            }
        }
        
        return $this->update($id, $data);
    }
}