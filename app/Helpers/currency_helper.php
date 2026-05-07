<?php

function secondary_currency_context(array $config): array
{
    $enabled = (($config['secondary_currency_enabled'] ?? false) == 1);
    $rate = (float)($config['secondary_currency_rate'] ?? 0);
    $decimals = (int)($config['secondary_currency_decimals'] ?? 0);
    $symbol = (string)($config['secondary_currency_symbol'] ?? '');
    $code = (string)($config['secondary_currency_code'] ?? '');

    return [
        'enabled' => $enabled,
        'rate' => $rate,
        'decimals' => $decimals,
        'symbol' => $symbol,
        'code' => $code,
        'show' => $enabled && $rate > 0,
    ];
}

function secondary_currency_label(string $symbol = '', string $code = ''): string
{
    $symbol = trim($symbol);
    $code = trim($code);

    if ($code !== '') {
        return $code;
    }

    if ($symbol !== '') {
        return $symbol;
    }

    return '';
}

function secondary_currency_amount(float $amount, float $rate = 1.0, int $decimals = 0, string $symbol = '', string $code = ''): string
{
    if ($rate <= 0) {
        return to_currency($amount);
    }

    $decimals = max(0, $decimals);
    $converted_amount = $amount * $rate;

    if ($decimals === 0) {
        $converted_amount = floor($converted_amount);
    } else {
        $precision = 10 ** $decimals;
        $converted_amount = floor($converted_amount * $precision) / $precision;
    }

    $prefix = trim($symbol !== '' ? $symbol : secondary_currency_label($symbol, $code));

    return $prefix !== ''
        ? $prefix . ' ' . number_format($converted_amount, $decimals, '.', ',')
        : number_format($converted_amount, $decimals, '.', ',');
}

function secondary_currency_dual_amount(float $amount, float $rate = 1.0, int $decimals = 0, string $symbol = '', string $code = ''): string
{
    if ($rate <= 0) {
        return to_currency($amount);
    }

    return secondary_currency_amount($amount, $rate, $decimals, $symbol, $code) . ' | ' . to_currency($amount);
}

function to_scnd_currency(float $amount, float $rate = 1.0, int $decimals = 0, string $symbol = '', string $code = ''): string
{
    return secondary_currency_amount($amount, $rate, $decimals, $symbol, $code);
}

function secondary_currency_rate_display(float $rate): string
{
    $formatted_rate = rtrim(rtrim(number_format($rate, 6, '.', ''), '0'), '.');

    return $formatted_rate === '' ? '0' : $formatted_rate;
}
