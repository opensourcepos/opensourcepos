<?php

use App\Libraries\Payments\PaymentProviderRegistry;
use CodeIgniter\Events\Events;

if (!function_exists('register_payment_provider')) {
    function register_payment_provider(App\Libraries\Payments\PaymentProviderInterface $provider): void
    {
        PaymentProviderRegistry::getInstance()->register($provider);
    }
}

if (!function_exists('get_payment_providers')) {
    function get_payment_providers(): array
    {
        return PaymentProviderRegistry::getInstance()->getProviders();
    }
}

if (!function_exists('get_enabled_payment_providers')) {
    function get_enabled_payment_providers(): array
    {
        return PaymentProviderRegistry::getInstance()->getEnabledProviders();
    }
}

if (!function_exists('get_enabled_payment_types')) {
    function get_enabled_payment_types(): array
    {
        return PaymentProviderRegistry::getInstance()->getEnabledPaymentTypes();
    }
}

if (!function_exists('get_payment_provider')) {
    function get_payment_provider(string $providerId): ?App\Libraries\Payments\PaymentProviderInterface
    {
        return PaymentProviderRegistry::getInstance()->getProvider($providerId);
    }
}

if (!function_exists('get_payment_provider_for_type')) {
    function get_payment_provider_for_type(string $paymentTypeKey): ?App\Libraries\Payments\PaymentProviderInterface
    {
        return PaymentProviderRegistry::getInstance()->getProviderForPaymentType($paymentTypeKey);
    }
}

if (!function_exists('payment_provider_content')) {
    function payment_provider_content(string $section, array $data = []): string
    {
        $results = Events::trigger("payment_view:{$section}", $data);
        $output = '';
        if (is_array($results)) {
            foreach ($results as $result) {
                if (is_string($result)) {
                    $output .= $result;
                }
            }
        } elseif (is_string($results)) {
            $output = $results;
        }
        return $output;
    }
}