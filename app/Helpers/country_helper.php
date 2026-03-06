<?php

namespace App\Helpers;

use Config\OSPOS;

/**
 * Country code helper for mapping country names to ISO 3166-1 alpha-2 codes
 */
if (!function_exists('getCountryCode')) {
    /**
     * Convert country name to ISO 3166-1 alpha-2 code
     * 
     * @param string $countryName Country name (full name in English)
     * @return string ISO 3166-1 alpha-2 code, or 'BE' as default for Belgium
     */
    function getCountryCode(string $countryName): string
    {
        if (empty($countryName)) {
            return 'BE'; // Default to Belgium
        }

        $countryMap = [
            // Major countries
            'Belgium' => 'BE',
            'Belgique' => 'BE',
            'België' => 'BE',
            'United States' => 'US',
            'USA' => 'US',
            'United States of America' => 'US',
            'United Kingdom' => 'GB',
            'UK' => 'GB',
            'Great Britain' => 'GB',
            'France' => 'FR',
            'Germany' => 'DE',
            'Deutschland' => 'DE',
            'Netherlands' => 'NL',
            'The Netherlands' => 'NL',
            'Nederland' => 'NL',
            'Italy' => 'IT',
            'Italia' => 'IT',
            'Spain' => 'ES',
            'España' => 'ES',
            'Poland' => 'PL',
            'Polska' => 'PL',
            'Portugal' => 'PT',
            'Sweden' => 'SE',
            'Sverige' => 'SE',
            'Norway' => 'NO',
            'Norge' => 'NO',
            'Denmark' => 'DK',
            'Danmark' => 'DK',
            'Finland' => 'FI',
            'Suomi' => 'FI',
            'Switzerland' => 'CH',
            'Suisse' => 'CH',
            'Schweiz' => 'CH',
            'Austria' => 'AT',
            'Österreich' => 'AT',
            'Ireland' => 'IE',
            'Luxembourg' => 'LU',
            'Greece' => 'GR',
            'Czech Republic' => 'CZ',
            'Czechia' => 'CZ',
            'Hungary' => 'HU',
            'Romania' => 'RO',
            'Bulgaria' => 'BG',
            'Slovakia' => 'SK',
            'Slovenia' => 'SI',
            'Estonia' => 'EE',
            'Latvia' => 'LV',
            'Lithuania' => 'LT',
            'Croatia' => 'HR',
            'Serbia' => 'RS',
            'Montenegro' => 'ME',
            'Bosnia and Herzegovina' => 'BA',
            'North Macedonia' => 'MK',
            'Albania' => 'AL',
            'Kosovo' => 'XK',
            'Turkey' => 'TR',
            'Türkiye' => 'TR',
            'Russia' => 'RU',
            'Russian Federation' => 'RU',
            'Ukraine' => 'UA',
            'Belarus' => 'BY',
            'Moldova' => 'MD',
            'Georgia' => 'GE',
            'Armenia' => 'AM',
            'Azerbaijan' => 'AZ',
            'Kazakhstan' => 'KZ',
            'Uzbekistan' => 'UZ',
            
            // Other major economies
            'China' => 'CN',
            'Japan' => 'JP',
            'South Korea' => 'KR',
            'Korea' => 'KR',
            'India' => 'IN',
            'Australia' => 'AU',
            'New Zealand' => 'NZ',
            'Canada' => 'CA',
            'Mexico' => 'MX',
            'Brazil' => 'BR',
            'Argentina' => 'AR',
            'Chile' => 'CL',
            'Colombia' => 'CO',
            'Peru' => 'PE',
            'South Africa' => 'ZA',
            'Egypt' => 'EG',
            'Nigeria' => 'NG',
            'Kenya' => 'KE',
            'Morocco' => 'MA',
            
            // If already ISO code, return as-is
            'BE' => 'BE',
            'US' => 'US',
            'GB' => 'GB',
            'FR' => 'FR',
            'DE' => 'DE',
            'NL' => 'NL',
            'IT' => 'IT',
            'ES' => 'ES',
            'PT' => 'PT',
            'SE' => 'SE',
            'NO' => 'NO',
            'DK' => 'DK',
            'FI' => 'FI',
            'CH' => 'CH',
            'AT' => 'AT',
            'IE' => 'IE',
            'LU' => 'LU',
            'GR' => 'GR',
            'CZ' => 'CZ',
            'HU' => 'HU',
            'RO' => 'RO',
            'BG' => 'BG',
            'SK' => 'SK',
            'SI' => 'SI',
            'EE' => 'EE',
            'LV' => 'LV',
            'LT' => 'LT',
            'HR' => 'HR',
            'RS' => 'RS',
            'ME' => 'ME',
            'BA' => 'BA',
            'MK' => 'MK',
            'AL' => 'AL',
            'TR' => 'TR',
            'RU' => 'RU',
            'UA' => 'UA',
        ];

        // Try exact match first
        $normalized = trim($countryName);
        if (isset($countryMap[$normalized])) {
            return $countryMap[$normalized];
        }

        // Try case-insensitive match
        $normalizedLower = strtolower($normalized);
        foreach ($countryMap as $key => $code) {
            if (strtolower($key) === $normalizedLower) {
                return $code;
            }
        }

        // Try partial match (e.g., "United States" → "US")
        foreach ($countryMap as $key => $code) {
            if (stripos($key, $normalized) !== false || stripos($normalized, $key) !== false) {
                return $code;
            }
        }

        // Try matching ISO code directly
        if (preg_match('/^[A-Z]{2}$/i', $normalized)) {
            return strtoupper($normalized);
        }

        // Check if the country_codes config has a default
        $config = config(OSPOS::class)->settings;
        if (isset($config['country_codes']) && !empty($config['country_codes'])) {
            $countries = explode(',', $config['country_codes']);
            if (!empty($countries)) {
                return strtoupper(trim($countries[0]));
            }
        }

        // Default to Belgium (for Peppol compliance in Belgium)
        return 'BE';
    }
}

if (!function_exists('getCurrencyCode')) {
    /**
     * Get ISO 4217 currency code for a country
     * 
     * @param string $countryCode ISO 3166-1 alpha-2 country code
     * @return string ISO 4217 currency code
     */
    function getCurrencyCode(string $countryCode): string
    {
        $currencyMap = [
            'BE' => 'EUR',
            'FR' => 'EUR',
            'DE' => 'EUR',
            'NL' => 'NL',
            'IT' => 'EUR',
            'ES' => 'EUR',
            'PT' => 'EUR',
            'IE' => 'EUR',
            'AT' => 'EUR',
            'LU' => 'EUR',
            'FI' => 'EUR',
            'GR' => 'EUR',
            'US' => 'USD',
            'GB' => 'GBP',
            'CH' => 'CHF',
            'JP' => 'JPY',
            'CN' => 'CNY',
            'CA' => 'CAD',
            'AU' => 'AUD',
            'NZ' => 'NZD',
            'IN' => 'INR',
            'BR' => 'BRL',
            'MX' => 'MXN',
            'ZA' => 'ZAR',
        ];

        return $currencyMap[$countryCode] ?? 'EUR'; // Default to EUR
    }
}