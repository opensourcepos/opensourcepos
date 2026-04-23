<?php

namespace App\Events;

use App\Libraries\Payments\PaymentProviderRegistry;
use CodeIgniter\Events\Events;
use Config\Services;

class PaymentEvents
{
    public static function initialize(): void
    {
        Events::on('payment_initiated', [static::class, 'onPaymentInitiated']);
        Events::on('payment_completed', [static::class, 'onPaymentCompleted']);
        Events::on('payment_failed', [static::class, 'onPaymentFailed']);
        Events::on('sale_completed', [static::class, 'onSaleCompleted']);
    }

    public static function onPaymentInitiated(array $data): void
    {
        log_message('debug', sprintf(
            'Payment initiated: type=%s, amount=%s, sale_id=%s',
            $data['payment_type'] ?? 'unknown',
            $data['amount'] ?? 0,
            $data['sale_id'] ?? 'pending'
        ));
    }

    public static function onPaymentCompleted(array $data): void
    {
        log_message('debug', sprintf(
            'Payment completed: type=%s, amount=%s, sale_id=%s',
            $data['payment_type'] ?? 'unknown',
            $data['amount'] ?? 0,
            $data['sale_id'] ?? 'pending'
        ));
    }

    public static function onPaymentFailed(array $data): void
    {
        log_message('warning', sprintf(
            'Payment failed: type=%s, amount=%s, error=%s',
            $data['payment_type'] ?? 'unknown',
            $data['amount'] ?? 0,
            $data['error'] ?? 'unknown error'
        ));
    }

    public static function onSaleCompleted(array $data): void
    {
        log_message('info', sprintf(
            'Sale completed: sale_id=%s, total=%s, payments=%s',
            $data['sale_id'] ?? 'unknown',
            $data['total'] ?? 0,
            json_encode($data['payments'] ?? [])
        ));
    }
}