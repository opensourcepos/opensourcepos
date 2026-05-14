<?php

namespace App\Libraries\Payments;

use CodeIgniter\Events\Events;

abstract class PaymentProviderBase implements PaymentProviderInterface
{
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getProviderId(): string
    {
        return static::class;
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getProviderDescription(): string
    {
        return '';
    }

    public function getPaymentTypes(): array
    {
        return [];
    }

    public function getIcon(?string $paymentType = null): ?string
    {
        return null;
    }

    public function getConfigView(): ?string
    {
        return null;
    }

    public function isAvailable(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']) && $settings['enabled'] === '1';
    }

    public function getSettings(): array
    {
        $settingsModel = model(\App\Models\Appconfig::class);
        $prefix = $this->getSettingsPrefix();
        
        $settings = [];
        $result = $settingsModel->like('key', $prefix . '_')->findAll();
        
        foreach ($result as $row) {
            $key = str_replace($prefix . '_', '', $row['key']);
            $settings[$key] = $row['value'];
        }
        
        return $settings;
    }

    public function saveSettings(array $settings): bool
    {
        $settingsModel = model(\App\Models\Appconfig::class);
        $prefix = $this->getSettingsPrefix();
        
        foreach ($settings as $key => $value) {
            $fullKey = $prefix . '_' . $key;
            $settingsModel->save(['key' => $fullKey, 'value' => $value]);
        }
        
        return true;
    }

    protected function getSettingsPrefix(): string
    {
        return 'payment_' . $this->getProviderId();
    }

    protected function logTransaction(
        string $transactionId,
        string $status,
        float $amount,
        string $currency = 'USD',
        ?int $saleId = null,
        array $metadata = []
    ): bool {
        $transactionModel = model(\App\Models\PaymentTransaction::class);
        
        $data = [
            'provider_id' => $this->getProviderId(),
            'transaction_id' => $transactionId,
            'status' => $status,
            'amount' => $amount,
            'currency' => $currency,
            'sale_id' => $saleId,
            'metadata' => json_encode($metadata),
        ];
        
        return $transactionModel->insert($data) !== false;
    }

    protected function updateTransactionStatus(string $transactionId, string $status, array $metadata = []): bool
    {
        $transactionModel = model(\App\Models\PaymentTransaction::class);
        $transaction = $transactionModel
            ->where('transaction_id', $transactionId)
            ->where('provider_id', $this->getProviderId())
            ->first();
            
        if (!$transaction) {
            return false;
        }
        
        $updateData = ['status' => $status];
        if (!empty($metadata)) {
            $existingMetadata = json_decode($transaction['metadata'] ?? '{}', true);
            $updateData['metadata'] = json_encode(array_merge($existingMetadata, $metadata));
        }
        
        return $transactionModel->update($transaction['id'], $updateData);
    }

    protected function getTransaction(string $transactionId): ?array
    {
        $transactionModel = model(\App\Models\PaymentTransaction::class);
        return $transactionModel
            ->where('transaction_id', $transactionId)
            ->where('provider_id', $this->getProviderId())
            ->first();
    }

    protected function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }

    protected function fire(string $event, array $data = []): void
    {
        Events::trigger("payment_{$event}", array_merge($data, ['provider' => $this->getProviderId()]));
    }
}