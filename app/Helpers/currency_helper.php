<?php

function secondary_currency_context(array $config, ?float $rateOverride = null): array
{
      $enabled = (($config['secondary_currency_enabled'] ?? false) == 1);
      $rate = $rateOverride !== null ? (float) $rateOverride : (float)($config['secondary_currency_rate'] ?? 0);
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

function secondary_currency_render_amount(float $amount, array $secondaryCurrency, bool $dual = false): string
{
    if (empty($secondaryCurrency['show']) || (float)($secondaryCurrency['rate'] ?? 0) <= 0) {
        return to_currency((string) $amount);
    }

    return $dual ? to_secondary_currency_dual($amount, $secondaryCurrency) : to_secondary_currency($amount, $secondaryCurrency);
}

function secondary_currency_render_rate(array $secondaryCurrency): string
{
    return secondary_currency_rate_display(
        (float) ($secondaryCurrency['rate'] ?? 0),
        (int) ($secondaryCurrency['decimals'] ?? 0)
    );
}

function secondary_currency_display_label(string $label, array $secondaryCurrency): string
{
    $currencyLabel = secondary_currency_label((string) ($secondaryCurrency['symbol'] ?? ''), (string) ($secondaryCurrency['code'] ?? ''));

    if ($currencyLabel === '') {
        return $label;
    }

    return trim($label . ' ' . $currencyLabel);
}

function secondary_currency_amount(float $amount, float $rate = 1.0, int $decimals = 0, string $symbol = '', string $code = ''): string
{
    return to_secondary_currency($amount, [
        'enabled' => $rate > 0,
        'rate' => $rate,
        'decimals' => $decimals,
        'symbol' => $symbol,
        'code' => $code,
        'show' => $rate > 0,
    ]);
}

function secondary_currency_dual_amount(float $amount, float $rate = 1.0, int $decimals = 0, string $symbol = '', string $code = ''): string
{
    return to_secondary_currency_dual($amount, [
        'enabled' => $rate > 0,
        'rate' => $rate,
        'decimals' => $decimals,
        'symbol' => $symbol,
        'code' => $code,
        'show' => $rate > 0,
    ]);
}

function to_secondary_currency(float $amount, ?array $secondaryCurrency = null): string
{
    $secondaryCurrency ??= secondary_currency_context(config(\Config\OSPOS::class)->settings);

    if (empty($secondaryCurrency['show']) || (float)($secondaryCurrency['rate'] ?? 0) <= 0) {
        return to_currency($amount);
    }

    $convertedAmount = $amount * (float) $secondaryCurrency['rate'];
    $prefix = secondary_currency_label((string) ($secondaryCurrency['symbol'] ?? ''), (string) ($secondaryCurrency['code'] ?? ''));

    return to_currency_with_symbol(
        (string) $convertedAmount,
        $prefix,
        (int) ($secondaryCurrency['decimals'] ?? 0)
    );
}

function to_secondary_currency_dual(float $amount, ?array $secondaryCurrency = null): string
{
    $secondaryCurrency ??= secondary_currency_context(config(\Config\OSPOS::class)->settings);

    if (empty($secondaryCurrency['show']) || (float)($secondaryCurrency['rate'] ?? 0) <= 0) {
        return to_currency($amount);
    }

    return to_secondary_currency($amount, $secondaryCurrency) . ' | ' . to_currency($amount);
}

function secondary_currency_rate_display(float $rate, int $precision = 0): string
{
    return format_locale_number((string) $rate, $precision, NumberFormatter::DECIMAL);
}
