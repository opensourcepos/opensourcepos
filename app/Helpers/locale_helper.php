<?php

use App\Models\Employee;
use Config\OSPOS;

/**
 * Returns the currently configured language code.
 *
 * @param bool $load_system_language When true, the system language is returned.
 * @return string Returns the default language code if a language code is not configured.
 */
function current_language_code(bool $load_system_language = false): string
{
    $employee = model(Employee::class);
    $config = config(OSPOS::class)->settings;

    if ($employee->is_logged_in() && !$load_system_language) {
        $employee_info = $employee->get_logged_in_employee_info();

        if (property_exists($employee_info, 'language_code') && !empty($employee_info->language_code)) {
            return $employee_info->language_code;
        }
    }

    $language_code = $config['language_code'];

    return empty($language_code) ? DEFAULT_LANGUAGE_CODE : $language_code;
}

/**
 * @param bool $load_system_language
 * @return string
 */
function current_language(bool $load_system_language = false): string
{
    $employee = model(Employee::class);
    $config = config(OSPOS::class)->settings;

    // Returns the language of the employee if set or system language if not
    if ($employee->is_logged_in() && !$load_system_language) {
        $employee_info = $employee->get_logged_in_employee_info();

        if (property_exists($employee_info, 'language') && !empty($employee_info->language)) {
            return $employee_info->language;
        }
    }

    $language = $config['language'];

    return empty($language) ? DEFAULT_LANGUAGE : $language;
}

/**
 * @return string[]
 */
function get_languages(): array
{
    $languages = [
        'ar-EG:arabic'                => 'Arabic (Egypt)',
        'ar-LB:arabic'                => 'Arabic (Lebanon)',
        'az:azerbaijani'              => 'Azerbaijani',
        'bg:bulgarian'                => 'Bulgarian',
        'bs:bosnian'                  => 'Bosnian',
        'ckb:centralkurdish'          => 'Kurdish (Central)',
        'cs:czech'                    => 'Czech',
        'da:danish'                   => 'Danish',
        'de-CH:german'                => 'German (Switzerland)',
        'de-DE:german'                => 'German (Germany)',
        'el:greek'                    => 'Greek',
        'en:english'                  => 'English (United States)',
        'en-GB:english'               => 'English (United Kingdom)',
        'es-ES:spanish'               => 'Spanish (Spain)',
        'es-MX:spanish'               => 'Spanish (Mexico)',
        'fa:persian'                  => 'Persian',
        'fr:french'                   => 'French',
        'he:hebrew'                   => 'Hebrew',
        'hr-HR:croatian'              => 'Croatian (Croatia)',
        'hu:hungarian'                => 'Hungarian',
        'hy:armenian'                 => 'Armenian',
        'id:indonesian'               => 'Indonesian',
        'it:italian'                  => 'Italian',
        'km:centralkhmer'             => 'Khmer (Central)',
        'lo:lao'                      => 'Lao',
        'ml:malayalam'                => 'Malayalam',
        'nb:norwegian'                => 'Norwegian Bokmål',
        'nl-BE:dutch'                 => 'Dutch (Belgium)',
        'nl-NL:dutch'                 => 'Dutch (Netherlands)',
        'pl:polish'                   => 'Polish',
        'pt-BR:portuguese'            => 'Portuguese (Brazil)',
        'ro:romanian'                 => 'Romanian',
        'ru:russian'                  => 'Russian',
        'sv:swedish'                  => 'Swedish',
        'ta:tamil'                    => 'Tamil',
        'th:thai'                     => 'Thai',
        'tl:tagalog'                  => 'Tagalog',
        'tr:turkish'                  => 'Turkish',
        'uk:ukrainian'                => 'Ukrainian',
        'ur:urdu'                     => 'Urdu',
        'vi:vietnamese'               => 'Vietnamese',
        'zh-Hans:simplified-chinese'  => 'Chinese (Simplified)',
        'zh-Hant:traditional-chinese' => 'Chinese (Traditional)'
    ];
    asort($languages);
    return $languages;
}

/**
 * @return string[]
 */
function get_timezones(): array
{
    return [
        'Pacific/Midway'                 => '(GMT-11:00) Midway Island, Samoa',
        'America/Adak'                   => '(GMT-10:00) Hawaii-Aleutian',
        'Etc/GMT+10'                     => '(GMT-10:00) Hawaii',
        'Pacific/Marquesas'              => '(GMT-09:30) Marquesas Islands',
        'Pacific/Gambier'                => '(GMT-09:00) Gambier Islands',
        'America/Anchorage'              => '(GMT-09:00) Alaska',
        'America/Ensenada'               => '(GMT-08:00) Tijuana, Baja California',
        'Etc/GMT+8'                      => '(GMT-08:00) Pitcairn Islands',
        'America/Los_Angeles'            => '(GMT-08:00) Pacific Time (US & Canada)',
        'America/Denver'                 => '(GMT-07:00) Mountain Time (US & Canada)',
        'America/Chihuahua'              => '(GMT-07:00) Chihuahua, La Paz, Mazatlan',
        'America/Dawson_Creek'           => '(GMT-07:00) Arizona',
        'America/Belize'                 => '(GMT-06:00) Saskatchewan, Central America',
        'America/Mexico_City'            => '(GMT-06:00) Guadalajara, Mexico City, Monterrey',
        'Chile/EasterIsland'             => '(GMT-06:00) Easter Island',
        'America/Chicago'                => '(GMT-06:00) Central Time (US & Canada)',
        'America/New_York'               => '(GMT-05:00) Eastern Time (US & Canada)',
        'America/Cancun'                 => '(GMT-05:00) Cancun',
        'America/Havana'                 => '(GMT-05:00) Cuba',
        'America/Bogota'                 => '(GMT-05:00) Bogota, Lima, Quito, Rio Branco',
        'America/Caracas'                => '(GMT-04:30) Caracas',
        'America/Santiago'               => '(GMT-04:00) Santiago',
        'America/La_Paz'                 => '(GMT-04:00) La Paz',
        'Atlantic/Stanley'               => '(GMT-04:00) Falkland Islands',
        'America/Campo_Grande'           => '(GMT-04:00) Brazil',
        'America/Goose_Bay'              => '(GMT-04:00) Atlantic Time (Goose Bay)',
        'America/Glace_Bay'              => '(GMT-04:00) Atlantic Time (Canada)',
        'America/St_Johns'               => '(GMT-03:30) Newfoundland',
        'America/Araguaina'              => '(GMT-03:00) UTC-3',
        'America/Montevideo'             => '(GMT-03:00) Montevideo',
        'America/Miquelon'               => '(GMT-03:00) Miquelon, St. Pierre',
        'America/Godthab'                => '(GMT-03:00) Greenland',
        'America/Argentina/Buenos_Aires' => '(GMT-03:00) Buenos Aires',
        'America/Sao_Paulo'              => '(GMT-03:00) Brasilia',
        'America/Noronha'                => '(GMT-02:00) Mid-Atlantic',
        'Atlantic/Cape_Verde'            => '(GMT-01:00) Cape Verde Is.',
        'Atlantic/Azores'                => '(GMT-01:00) Azores',
        'Europe/Belfast'                 => '(GMT) Greenwich Mean Time : Belfast',
        'Europe/Dublin'                  => '(GMT) Greenwich Mean Time : Dublin',
        'Europe/Lisbon'                  => '(GMT) Greenwich Mean Time : Lisbon',
        'Europe/London'                  => '(GMT) Greenwich Mean Time : London',
        'Africa/Abidjan'                 => '(GMT) Monrovia, Reykjavik',
        'Europe/Amsterdam'               => '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',
        'Europe/Belgrade'                => '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague',
        'Europe/Brussels'                => '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris',
        'Africa/Algiers'                 => '(GMT+01:00) West Central Africa',
        'Africa/Windhoek'                => '(GMT+01:00) Windhoek',
        'Asia/Beirut'                    => '(GMT+02:00) Beirut',
        'Africa/Cairo'                   => '(GMT+02:00) Cairo',
        'Asia/Gaza'                      => '(GMT+02:00) Gaza',
        'Africa/Blantyre'                => '(GMT+02:00) Harare, Pretoria',
        'Asia/Jerusalem'                 => '(GMT+02:00) Jerusalem',
        'Europe/Minsk'                   => '(GMT+02:00) Minsk',
        'Asia/Damascus'                  => '(GMT+02:00) Syria',
        'Europe/Moscow'                  => '(GMT+03:00) Moscow, St. Petersburg, Volgograd',
        'Africa/Addis_Ababa'             => '(GMT+03:00) Nairobi',
        'Asia/Tehran'                    => '(GMT+03:30) Tehran',
        'Asia/Dubai'                     => '(GMT+04:00) Abu Dhabi, Muscat',
        'Asia/Yerevan'                   => '(GMT+04:00) Yerevan',
        'Asia/Kabul'                     => '(GMT+04:30) Kabul',
        'Asia/Baku'                      => '(GMT+04:00) Baku',
        'Asia/Yekaterinburg'             => '(GMT+05:00) Ekaterinburg',
        'Asia/Karachi'                   => '(GMT+05:00) Karachi, Islamabad',
        'Asia/Tashkent'                  => '(GMT+05:00) Tashkent',
        'Asia/Kolkata'                   => '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi',
        'Asia/Katmandu'                  => '(GMT+05:45) Kathmandu',
        'Asia/Dhaka'                     => '(GMT+06:00) Astana, Dhaka',
        'Asia/Novosibirsk'               => '(GMT+06:00) Novosibirsk',
        'Asia/Rangoon'                   => '(GMT+06:30) Yangon (Rangoon)',
        'Asia/Bangkok'                   => '(GMT+07:00) Bangkok, Hanoi, Jakarta',
        'Asia/Krasnoyarsk'               => '(GMT+07:00) Krasnoyarsk',
        'Asia/Hong_Kong'                 => '(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi',
        'Asia/Irkutsk'                   => '(GMT+08:00) Irkutsk, Ulaan Bataar',
        'Australia/Perth'                => '(GMT+08:00) Perth',
        'Australia/Eucla'                => '(GMT+08:45) Eucla',
        'Asia/Tokyo'                     => '(GMT+09:00) Osaka, Sapporo, Tokyo',
        'Asia/Seoul'                     => '(GMT+09:00) Seoul',
        'Asia/Yakutsk'                   => '(GMT+09:00) Yakutsk',
        'Australia/Adelaide'             => '(GMT+09:30) Adelaide',
        'Australia/Darwin'               => '(GMT+09:30) Darwin',
        'Australia/Brisbane'             => '(GMT+10:00) Brisbane',
        'Australia/Hobart'               => '(GMT+10:00) Hobart',
        'Asia/Vladivostok'               => '(GMT+10:00) Vladivostok',
        'Australia/Lord_Howe'            => '(GMT+10:30) Lord Howe Island',
        'Etc/GMT-11'                     => '(GMT+11:00) Solomon Is., New Caledonia',
        'Asia/Magadan'                   => '(GMT+11:00) Magadan',
        'Pacific/Norfolk'                => '(GMT+11:30) Norfolk Island',
        'Asia/Anadyr'                    => '(GMT+12:00) Anadyr, Kamchatka',
        'Pacific/Auckland'               => '(GMT+12:00) Auckland, Wellington',
        'Etc/GMT-12'                     => '(GMT+12:00) Fiji, Kamchatka, Marshall Is.',
        'Pacific/Chatham'                => '(GMT+12:45) Chatham Islands',
        'Pacific/Tongatapu'              => '(GMT+13:00) Nuku\'alofa',
        'Pacific/Kiritimati'             => '(GMT+14:00) Kiritimati'
    ];
}

/**
 * @return string[]
 */
function get_dateformats(): array
{
    return [
        'd/m/Y' => 'dd/mm/yyyy',
        'd.m.Y' => 'dd.mm.yyyy',
        'm/d/Y' => 'mm/dd/yyyy',
        'Y/m/d' => 'yyyy/mm/dd',
        'd/m/y' => 'dd/mm/yy',
        'm/d/y' => 'mm/dd/yy',
        'y/m/d' => 'yy/mm/dd'
    ];
}

/**
 * @return string[]
 */
function get_timeformats(): array
{
    return [
        'H:i:s'   => 'hh:mm:ss (24h)',
        'h:i:s a' => 'hh:mm:ss am/pm',
        'h:i:s A' => 'hh:mm:ss AM/PM'
    ];
}


/**
 * Gets the payment options
 */
function get_payment_options(): array
{
    $payments = [];
    $config = config(OSPOS::class)->settings;

    // TODO: This needs to be switched to a switch statement
    if ($config['payment_options_order'] == 'debitcreditcash') {    // TODO: ===
        $payments[lang('Sales.debit')] = lang('Sales.debit');
        $payments[lang('Sales.credit')] = lang('Sales.credit');
        $payments[lang('Sales.cash')] = lang('Sales.cash');
    } elseif ($config['payment_options_order'] == 'debitcashcredit') {    // TODO: ===
        $payments[lang('Sales.debit')] = lang('Sales.debit');
        $payments[lang('Sales.cash')] = lang('Sales.cash');
        $payments[lang('Sales.credit')] = lang('Sales.credit');
    } elseif ($config['payment_options_order'] == 'creditdebitcash') {    // TODO: ===
        $payments[lang('Sales.credit')] = lang('Sales.credit');
        $payments[lang('Sales.debit')] = lang('Sales.debit');
        $payments[lang('Sales.cash')] = lang('Sales.cash');
    } elseif ($config['payment_options_order'] == 'creditcashdebit') {    // TODO: ===
        $payments[lang('Sales.credit')] = lang('Sales.credit');
        $payments[lang('Sales.cash')] = lang('Sales.cash');
        $payments[lang('Sales.debit')] = lang('Sales.debit');
    } else { // Default: if ($config['payment_options_order == 'cashdebitcredit')
        $payments[lang('Sales.cash')] = lang('Sales.cash');
        $payments[lang('Sales.debit')] = lang('Sales.debit');
        $payments[lang('Sales.credit')] = lang('Sales.credit');
    }

    $payments[lang('Sales.due')] = lang('Sales.due');
    $payments[lang('Sales.check')] = lang('Sales.check');

    // If India (list of country codes include India) then include Unified Payment Interface
    if (stripos($config['country_codes'], 'IN') !== false) {
        $payments[lang('Sales.upi')] = lang('Sales.upi');
    }

    return $payments;
}

/**
 * Determines if the current currency symbol is on the right side of the amount
 *
 * @return bool true is returned when the symbol should be displayed to the right of the amount. False otherwise.
 */
function is_right_side_currency_symbol(): bool
{
    $config = config(OSPOS::class)->settings;
    $fmt = new NumberFormatter($config['number_locale'], NumberFormatter::CURRENCY);
    $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, $config['currency_symbol']);

    return !preg_match('/^¤/', $fmt->getPattern());
}

/**
 * Returns the number of decimals to use in Quantities.
 *
 * @return int The number of decimals to include in the quantity.
 */
function quantity_decimals(): int
{
    $config = config(OSPOS::class)->settings;
    return $config['quantity_decimals'] ?? 0;
}

/**
 * @return int
 */
function totals_decimals(): int
{
    $config = config(OSPOS::class)->settings;
    return $config['currency_decimals'] ?? 0;
}

/**
 * @return int
 */
function cash_decimals(): int
{
    $config = config(OSPOS::class)->settings;
    return $config['cash_decimals'] ?? 0;
}

/**
 * @return int
 */
function tax_decimals(): int
{
    $config = config(OSPOS::class)->settings;
    return $config['tax_decimals'] ?? 0;
}

/**
 * @param int $date
 * @return string
 */
function to_date(int $date = DEFAULT_DATE): string
{
    $config = config(OSPOS::class)->settings;
    return date($config['dateformat'], $date);
}

/**
 * @param int $datetime
 * @return string
 */
function to_datetime(int $datetime = DEFAULT_DATETIME): string
{
    $config = config(OSPOS::class)->settings;
    return date($config['dateformat'] . ' ' . $config['timeformat'], $datetime);
}

/**
 * @param string|null $number
 * @return string
 */
function to_currency(?string $number): string
{
    return to_decimals($number, 'currency_decimals', NumberFormatter::CURRENCY);
}

/**
 * @param string|null $number
 * @return string
 */
function to_currency_no_money(?string $number): string
{
    return to_decimals($number, 'currency_decimals');
}

/**
 * @param string|null $number
 * @return string
 */
function to_currency_tax(?string $number): string
{
    $config = config(OSPOS::class)->settings;

    if ($config['tax_included']) {    // TODO: ternary notation
        return to_decimals($number, 'tax_decimals', NumberFormatter::CURRENCY);
    } else {
        return to_decimals($number, 'currency_decimals', NumberFormatter::CURRENCY);
    }
}

/**
 * @param $number
 * @return string
 */
function to_tax_decimals($number): string
{
    // TODO: When the tax array is empty the value passed in is an empty string,  For now I "untyped" it to get past
    // the issue because I don't understand why an empty string is being passed in when I know the array is empty.
    // It looks like it must be creating a String value on the fly because the form is referring to the index 0 when
    // there IS no index[0] row in the table

    // Taxes that are null, '' or 0 don't need to be displayed
    // NOTE: do not remove this line otherwise the items edit form will show a tax with 0, and it will save it
    if (empty($number)) {
        return $number;
    }

    return to_decimals($number, 'tax_decimals');
}

/**
 * @param string|null $number
 * @return string
 */
function to_quantity_decimals(?string $number): string
{
    return to_decimals($number, 'quantity_decimals');
}

/**
 * Converts a string to locale-specific number format for display.
 *
 * @param string|null $decimals
 * @param int $type
 * @return string
 */
function to_decimals(?string $number, ?string $decimals = null, int $type = NumberFormatter::DECIMAL): string
{
    if (!isset($number)) {
        return '';
    }

    $config = config(OSPOS::class)->settings;
    $fmt = new NumberFormatter($config['number_locale'], $type);
    $fmt->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, empty($decimals) ? DEFAULT_PRECISION : $config[$decimals]);
    $fmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, empty($decimals) ? DEFAULT_PRECISION : $config[$decimals]);

    if (empty($config['thousands_separator'])) {
        $fmt->setTextAttribute(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
    }
    $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, $config['currency_symbol']);

    return $fmt->format((float) $number);
}

/**
 * @param string $number
 * @return false|float|int|mixed|string
 */
function parse_quantity(string $number): mixed
{
    return parse_decimals($number, quantity_decimals());
}

/**
 * @param string $number
 * @return false|float|int|mixed|string
 */
function parse_tax(string $number): mixed
{
    return parse_decimals($number, tax_decimals());
}

/**
 * @param string $number
 * @param int|null $decimals
 * @return false|float|int|mixed|string
 */
function parse_decimals(string $number, ?int $decimals = null): mixed
{
    if (empty($number)) {
        return $number;
    }


    $config = config(OSPOS::class)->settings;

    $fmt = new NumberFormatter($config['number_locale'], NumberFormatter::DECIMAL);

    if (!$decimals) {
        $decimals = intVal($config['currency_decimals']);
        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
    }

    if (empty($config['thousands_separator'])) {
        $fmt->setTextAttribute(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
    }

    try {
        $locale_safe_number = $fmt->parse($number);

        if (
            $locale_safe_number === false
            || $locale_safe_number > MAX_PRECISION
            || $locale_safe_number > 1.e14
        ) {
            return false;
        }

        return (float) $locale_safe_number;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Time locale conversion utility
 */
function dateformat_momentjs(string $php_format): string
{
    $SYMBOLS_MATCHING = [
        'd' => 'DD',
        'D' => 'ddd',
        'j' => 'D',
        'l' => 'dddd',
        'N' => 'E',
        'S' => 'o',
        'w' => 'e',
        'z' => 'DDD',
        'W' => 'W',
        'F' => 'MMMM',
        'm' => 'MM',
        'M' => 'MMM',
        'n' => 'M',
        't' => '', // No equivalent
        'L' => '', // No equivalent
        'o' => 'YYYY',
        'Y' => 'YYYY',
        'y' => 'YY',
        'a' => 'a',
        'A' => 'A',
        'B' => '', // No equivalent
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => 'SSS',
        'e' => 'zz', // Deprecated since version $1.6.0 of moment.js
        'I' => '', // No equivalent
        'O' => '', // No equivalent
        'P' => '', // No equivalent
        'T' => '', // No equivalent
        'Z' => '', // No equivalent
        'c' => '', // No equivalent
        'r' => '', // No equivalent
        'U' => 'X'
    ];

    return strtr($php_format, $SYMBOLS_MATCHING);
}

/**
 * @return string
 */
function dateformat_mysql(): string
{
    $config = config(OSPOS::class)->settings;
    $php_format = $config['dateformat'];

    $SYMBOLS_MATCHING = [
        // Day
        'd' => '%d',
        'D' => '%a',
        'j' => '%e',
        'l' => '%W',
        'N' => '',
        'S' => '',
        'w' => '',
        'z' => '',
        // Week
        'W' => '',
        // Month
        'F' => '',
        'm' => '%m',
        'M' => '%b',
        'n' => '%c',
        't' => '',
        // Year
        'L' => '',
        'o' => '',
        'Y' => '%Y',
        'y' => '%y',
        // Time
        'a' => '',
        'A' => '%p',
        'B' => '',
        'g' => '%l',
        'G' => '%k',
        'h' => '%H',
        'H' => '%k',
        'i' => '%i',
        's' => '%S',
        'u' => '%f'
    ];

    return strtr($php_format, $SYMBOLS_MATCHING);
}

/**
 * @param string $php_format
 * @return string
 */
function dateformat_bootstrap(string $php_format): string
{
    $SYMBOLS_MATCHING = [
        // Day
        'd' => 'dd',
        'D' => 'd',
        'j' => 'd',
        'l' => 'dd',
        'N' => '',
        'S' => '',
        'w' => '',
        'z' => '',
        // Week
        'W' => '',
        // Month
        'F' => 'MM',
        'm' => 'mm',
        'M' => 'M',
        'n' => 'm',
        't' => '',
        // Year
        'L' => '',
        'o' => '',
        'Y' => 'yyyy',
        'y' => 'yy',
        // Time
        'a' => 'p',
        'A' => 'P',
        'B' => '',
        'g' => 'H',
        'G' => 'h',
        'h' => 'HH',
        'H' => 'hh',
        'i' => 'ii',
        's' => 'ss',
        'u' => ''
    ];

    return strtr($php_format, $SYMBOLS_MATCHING);
}

/**
 * @param string $date
 * @return bool
 */
function valid_date(string $date): bool    // TODO: need a better name for $date.  Perhaps $candidate. Also the function name would be better as is_valid_date()
{
    $config = config(OSPOS::class)->settings;
    return (DateTime::createFromFormat($config['dateformat'], $date));
}

/**
 * @param string $decimal
 * @return bool
 */
function valid_decimal(string $decimal): bool    // TODO: need a better name for $decimal.  Perhaps $candidate. Also the function name would be better as is_valid_decimal()
{
    return (preg_match('/^(\d*\.)?\d+$/', $decimal) === 1);
}

/**
 * @param array $data
 * @return array
 */
function encode_array(array $data): array
{
    array_walk($data, function (&$value, $key) {
        $value = rawurlencode($value);
    });

    return $data;
}

/**
 * @param array $data
 * @return array
 */
function decode_array(array $data): array
{
    array_walk($data, function (&$value, $key) {
        $value = rawurldecode($value);
    });

    return $data;
}
