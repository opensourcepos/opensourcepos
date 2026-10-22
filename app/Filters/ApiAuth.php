<?php

namespace App\Filters;

use App\Models\ApiKey;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ApiAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $apiKey = $request->getHeaderLine('X-API-Key');
        
        if (empty($apiKey)) {
            return $this->unauthorized('API key required');
        }
        
        $apiKeyModel = model(ApiKey::class);
        $employeeId = $apiKeyModel->validateKey($apiKey);
        
        if (!$employeeId) {
            return $this->unauthorized('Invalid or expired API key');
        }
        
        $request->employeeId = $employeeId;
        Services::set('apiEmployeeId', $employeeId);
        
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return $response;
    }

    private function unauthorized(string $message): ResponseInterface
    {
        return Services::response()
            ->setStatusCode(401)
            ->setJSON([
                'success' => false,
                'message' => $message
            ]);
    }
}