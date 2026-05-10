<?php

namespace App\Libraries;

use Config\Services;

class SecondaryCurrencyFeedLib
{
    private const DEFAULT_FEED_URL = 'https://open.er-api.com/v6/latest/{base}';
    private const DEFAULT_CA_BUNDLE = WRITEPATH . 'certs/cacert.pem';

    public function getDefaultFeedUrl(): string
    {
        return self::DEFAULT_FEED_URL;
    }

    public function buildFeedUrl(array $config): string
    {
        $template = trim((string) ($config['secondary_currency_feed_url'] ?? ''));
        if ($template === '') {
            $template = self::DEFAULT_FEED_URL;
        }

        $baseCurrency = rawurlencode(trim((string) ($config['currency_code'] ?? '')));
        $secondaryCurrency = rawurlencode(trim((string) ($config['secondary_currency_code'] ?? '')));

        return str_replace(
            ['{base}', '{quote}'],
            [$baseCurrency, $secondaryCurrency],
            $template
        );
    }

    public function getCertificateBundlePath(): ?string
    {
        $bundlePath = self::DEFAULT_CA_BUNDLE;

        return is_file($bundlePath) ? $bundlePath : null;
    }

    public function refreshRate(array $config, bool $force = false): array
    {
        if (empty($config['secondary_currency_enabled'])) {
            return [
                'success' => false,
                'message' => lang('Config.secondary_currency_disabled')
            ];
        }

        if (!$force && empty($config['secondary_currency_auto_enabled'])) {
            return [
                'success' => false,
                'message' => lang('Config.secondary_currency_auto_refresh_disabled')
            ];
        }

        $baseCurrency = trim((string) ($config['currency_code'] ?? ''));
        $secondaryCurrency = trim((string) ($config['secondary_currency_code'] ?? ''));

        if ($baseCurrency === '' || $secondaryCurrency === '') {
            return [
                'success' => false,
                'message' => lang('Config.secondary_currency_codes_required')
            ];
        }

        if ($baseCurrency === $secondaryCurrency) {
            return [
                'success' => false,
                'message' => lang('Config.secondary_currency_codes_must_differ')
            ];
        }

        $url = $this->buildFeedUrl($config);
        $curlOptions = [
            'timeout' => 15,
            'http_errors' => false,
        ];

        $certificateBundlePath = $this->getCertificateBundlePath();
        if ($certificateBundlePath !== null) {
            $curlOptions['verify'] = $certificateBundlePath;
        }

        $client = Services::curlrequest($curlOptions);

        try {
            $response = $client->get($url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        } catch (\Throwable $throwable) {
            return [
                'success' => false,
                'message' => lang('Config.secondary_currency_feed_request_failed', [$throwable->getMessage()]),
            ];
        }

        if ($response->getStatusCode() !== 200) {
            return [
                'success' => false,
                'message' => lang('Config.secondary_currency_feed_http_error', [(string) $response->getStatusCode()])
            ];
        }

        $payload = json_decode((string) $response->getBody(), true);
        if (!is_array($payload)) {
            return [
                'success' => false,
                'message' => lang('Config.secondary_currency_feed_invalid_json')
            ];
        }

        $rate = $this->extractRate($payload, $secondaryCurrency);
        if ($rate === null || $rate <= 0) {
            return [
                'success' => false,
                'message' => lang('Config.secondary_currency_feed_invalid_rate', [$secondaryCurrency])
            ];
        }

        return [
            'success' => true,
            'rate' => $rate,
            'message' => lang('Config.secondary_currency_refresh_successful'),
            'feed_url' => $url,
        ];
    }

    public function extractRate(array $payload, string $secondaryCurrency): ?float
    {
        $secondaryCurrency = strtoupper(trim($secondaryCurrency));
        if ($secondaryCurrency === '') {
            return null;
        }

        $candidatePaths = [
            ['rates', $secondaryCurrency],
            ['conversion_rates', $secondaryCurrency],
            ['data', $secondaryCurrency],
        ];

        foreach ($candidatePaths as $path) {
            $value = $payload[$path[0]][$path[1]] ?? null;
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        if (is_numeric($payload[$secondaryCurrency] ?? null)) {
            return (float) $payload[$secondaryCurrency];
        }

        return null;
    }
}
