<?php

namespace App\Controllers;

use App\Models\ApiKey;
use App\Models\Employee;

class ApiKeys extends Secure_Controller
{
    protected ApiKey $apiKeyModel;
    
    public function __construct()
    {
        parent::__construct('api_keys');
        $this->apiKeyModel = model(ApiKey::class);
    }

    public function index(): void
    {
        $employeeId = $this->employee->get_logged_in_employee_info()->person_id;
        $keys = $this->apiKeyModel->getKeysForEmployee($employeeId);
        
        echo view('api_keys/manage', [
            'keys' => $keys,
            'employee_info' => $this->employee->get_logged_in_employee_info()
        ]);
    }

    public function generate(): void
    {
        $employeeId = $this->employee->get_logged_in_employee_info()->person_id;
        $name = $this->request->getPost('name');
        $expiresAt = $this->request->getPost('expires_at') ?: null;
        
        $apiKey = $this->apiKeyModel->generateKey($employeeId, $name, $expiresAt);
        
        if ($apiKey) {
            echo json_encode([
                'success' => true,
                'message' => lang('Api_keys.key_generated'),
                'apiKey' => $apiKey,
                'keyPrefix' => substr($apiKey, 0, 12) . '...'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => lang('Api_keys.key_generation_failed')
            ]);
        }
    }

    public function revoke(int $apiKeyId): void
    {
        $employeeId = $this->employee->get_logged_in_employee_info()->person_id;
        
        $success = $this->apiKeyModel->revokeKey($apiKeyId, $employeeId);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? lang('Api_keys.key_revoked') : lang('Api_keys.key_revoke_failed')
        ]);
    }

    public function regenerate(int $apiKeyId): void
    {
        $employeeId = $this->employee->get_logged_in_employee_info()->person_id;
        
        $newKey = $this->apiKeyModel->regenerateKey($apiKeyId, $employeeId);
        
        if ($newKey) {
            echo json_encode([
                'success' => true,
                'message' => lang('Api_keys.key_regenerated'),
                'apiKey' => $newKey,
                'keyPrefix' => substr($newKey, 0, 12) . '...'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => lang('Api_keys.key_regeneration_failed')
            ]);
        }
    }
}