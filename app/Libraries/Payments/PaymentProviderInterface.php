<?php

namespace App\Libraries\Payments;

interface PaymentProviderInterface
{
    public function getProviderId(): string;

    public function getProviderName(): string;

    public function getProviderDescription(): string;

    public function getVersion(): string;

    public function getPaymentTypes(): array;

    public function getIcon(?string $paymentType = null): ?string;

    public function initiatePayment(float $amount, string $currency, array $options = []): array;

    public function processCallback(array $data): array;

    public function getPaymentStatus(string $transactionId): array;

    public function refund(string $transactionId, float $amount, string $reason = ''): array;

    public function cancel(string $transactionId): array;

    public function isAvailable(): bool;

    public function getSettings(): array;

    public function saveSettings(array $settings): bool;

    public function getConfigView(): ?string;
}