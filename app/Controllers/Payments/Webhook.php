<?php

namespace App\Controllers\Payments;

use App\Controllers\BaseController;
use App\Libraries\Payments\PaymentProviderRegistry;
use CodeIgniter\HTTP\ResponseInterface;

class Webhook extends BaseController
{
    public function handle(string $providerId): ResponseInterface
    {
        $provider = PaymentProviderRegistry::getInstance()->getProvider($providerId);
        
        if ($provider === null) {
            log_message('error', "Webhook received for unknown provider: {$providerId}");
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'error' => 'Provider not found'
            ]);
        }
        
        $rawInput = $this->request->getBody();
        $data = json_decode($rawInput, true) ?? [];
        
        if (empty($rawInput)) {
            $data = $this->request->getPost();
        }
        
        try {
            $result = $provider->processCallback($data);
            
            if ($result['success'] ?? false) {
                log_message('info', "Webhook processed successfully for provider: {$providerId}", $result);
                return $this->response->setStatusCode(200)->setJSON($result);
            }
            
            log_message('warning', "Webhook processing failed for provider: {$providerId}", $result);
            return $this->response->setStatusCode(400)->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', "Webhook exception for provider {$providerId}: " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Internal server error'
            ]);
        }
    }

    public function status(string $providerId, string $transactionId): ResponseInterface
    {
        $provider = PaymentProviderRegistry::getInstance()->getProvider($providerId);
        
        if ($provider === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'error' => 'Provider not found'
            ]);
        }
        
        try {
            $result = $provider->getPaymentStatus($transactionId);
            return $this->response->setStatusCode(200)->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', "Status check exception for provider {$providerId}: " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Internal server error'
            ]);
        }
    }
}