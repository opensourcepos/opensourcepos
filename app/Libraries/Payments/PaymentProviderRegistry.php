<?php

namespace App\Libraries\Payments;

class PaymentProviderRegistry
{
    private static ?PaymentProviderRegistry $instance = null;
    private array $providers = [];
    private bool $initialized = false;

    private function __construct()
    {
    }

    public static function getInstance(): PaymentProviderRegistry
    {
        if (self::$instance === null) {
            self::$instance = new PaymentProviderRegistry();
        }
        return self::$instance;
    }

    public function register(PaymentProviderInterface $provider): void
    {
        $providerId = $provider->getProviderId();
        if (!isset($this->providers[$providerId])) {
            $this->providers[$providerId] = $provider;
        }
    }

    public function unregister(string $providerId): void
    {
        unset($this->providers[$providerId]);
    }

    public function getProvider(string $providerId): ?PaymentProviderInterface
    {
        return $this->providers[$providerId] ?? null;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getEnabledProviders(): array
    {
        $enabled = [];
        foreach ($this->providers as $provider) {
            if ($provider->isAvailable()) {
                $enabled[$provider->getProviderId()] = $provider;
            }
        }
        return $enabled;
    }

    public function getEnabledPaymentTypes(): array
    {
        $paymentTypes = [];
        foreach ($this->getEnabledProviders() as $provider) {
            $providerTypes = $provider->getPaymentTypes();
            foreach ($providerTypes as $key => $label) {
                $paymentTypes[$key] = $label;
            }
        }
        return $paymentTypes;
    }

    public function getProviderForPaymentType(string $paymentTypeKey): ?PaymentProviderInterface
    {
        foreach ($this->providers as $provider) {
            $types = $provider->getPaymentTypes();
            if (isset($types[$paymentTypeKey])) {
                return $provider;
            }
        }
        return null;
    }

    public function getProviderByTransactionId(string $transactionId): ?PaymentProviderInterface
    {
        $transactionModel = model(\App\Models\PaymentTransaction::class);
        $transaction = $transactionModel->where('transaction_id', $transactionId)->first();
        
        if (!$transaction) {
            return null;
        }
        
        return $this->getProvider($transaction['provider_id']);
    }

    public function hasProviders(): bool
    {
        return !empty($this->providers);
    }

    public function count(): int
    {
        return count($this->providers);
    }
}