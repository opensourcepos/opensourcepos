<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiKey extends Model
{
    protected $table = 'api_keys';
    protected $primaryKey = 'api_key_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'employee_id',
        'key_hash',
        'key_prefix',
        'name',
        'last_used',
        'expires_at',
        'disabled'
    ];
    
    protected $useTimestamps = false;
    protected $createdField = 'created';
    
    private const KEY_PREFIX = 'ospos_';
    private const KEY_BYTES = 32;

    public function generateKey(int $employeeId, ?string $name = null, ?string $expiresAt = null): string|false
    {
        $rawKey = bin2hex(random_bytes(self::KEY_BYTES));
        $apiKey = self::KEY_PREFIX . $rawKey;
        
        $keyHash = hash('sha256', $apiKey);
        $keyPrefix = substr($apiKey, 0, 12);
        
        $data = [
            'employee_id' => $employeeId,
            'key_hash' => $keyHash,
            'key_prefix' => $keyPrefix,
            'name' => $name,
            'expires_at' => $expiresAt
        ];
        
        if ($this->insert($data)) {
            return $apiKey;
        }
        
        return false;
    }

    public function validateKey(string $apiKey): int|false
    {
        if (!str_starts_with($apiKey, self::KEY_PREFIX)) {
            return false;
        }
        
        if (strlen($apiKey) !== strlen(self::KEY_PREFIX) + (self::KEY_BYTES * 2)) {
            return false;
        }
        
        $keyHash = hash('sha256', $apiKey);
        
        $builder = $this->builder();
        $builder->where('key_hash', $keyHash);
        $builder->where('disabled', 0);
        $builder->groupStart();
        $builder->where('expires_at IS NULL');
        $builder->orWhere('expires_at >', date('Y-m-d H:i:s'));
        $builder->groupEnd();
        
        $result = $builder->get()->getRow();
        
        if ($result) {
            $this->update($result->api_key_id, ['last_used' => date('Y-m-d H:i:s')]);
            return (int) $result->employee_id;
        }
        
        return false;
    }

    public function getKeysForEmployee(int $employeeId): array
    {
        $builder = $this->builder();
        $builder->where('employee_id', $employeeId);
        $builder->orderBy('created', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    public function revokeKey(int $apiKeyId, int $employeeId): bool
    {
        $builder = $this->builder();
        $builder->where('api_key_id', $apiKeyId);
        $builder->where('employee_id', $employeeId);
        
        return $builder->update(['disabled' => 1]) !== false;
    }

    public function regenerateKey(int $apiKeyId, int $employeeId): string|false
    {
        $existingKey = $this->builder()
            ->getWhere([
                'api_key_id' => $apiKeyId,
                'employee_id' => $employeeId
            ])
            ->getRow();
        
        if (!$existingKey) {
            return false;
        }
        
        $newKey = $this->generateKey(
            $employeeId,
            $existingKey->name,
            $existingKey->expires_at
        );
        
        if ($newKey) {
            $this->delete($apiKeyId);
            return $newKey;
        }
        
        return false;
    }

    public function cleanupExpired(): int
    {
        $builder = $this->builder();
        $builder->where('disabled', 0);
        $builder->where('expires_at <', date('Y-m-d H:i:s'));
        $builder->where('expires_at IS NOT NULL');
        
        $expiredKeys = $builder->get()->getResultArray();
        $count = 0;
        
        foreach ($expiredKeys as $key) {
            if ($this->update($key['api_key_id'], ['disabled' => 1])) {
                $count++;
            }
        }
        
        return $count;
    }
}