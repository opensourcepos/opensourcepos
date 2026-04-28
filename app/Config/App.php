<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\DatabaseHandler;

class App extends BaseConfig
{
    /**
     * This is the code version of the Open Source Point of Sale you're running.
     *
     * @var string
     */
    public string $application_version = '3.4.2';

    /**
     * This is the commit hash for the version you are currently using.
     *
     * @var string
     */
    public string $commit_sha1 = 'dev';

    /**
     * Logs are stored in writable/logs
     *
     * @var bool
     */
    public bool $db_log_enabled = false;

    /**
     * DB Query Log only long-running queries
     *
     * @var bool
     */
    public bool $db_log_only_long = false;

    /**
     * Defines whether to require/reroute to HTTPS
     *
     * @var bool
     */
    public bool $https_on;    // Set in the constructor

    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * URL to your CodeIgniter root. Typically, this will be your base URL,
     * WITH a trailing slash:
     *
     * E.g., http://example.com/
     */
    public string $baseURL;    // Defined in the constructor

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     * If you want to accept multiple Hostnames, set this.
     * If empty in production, the application will fail to start.
     * In development, it will fall back to 'localhost' with a warning.
     * Configure via .env file (comma-separated list):
     *   app.allowedHostnames = 'example.com,www.example.com'
     *
     * E.g.,
     *   app.allowedHostnames = 'localhost'
     * also accepts 'http://media.example.com/' and 'http://accounts.example.com/':
     *     ['media.example.com', 'accounts.example.com']
     *
     * @var list<string>
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     *
     * Typically, this will be your `index.php` file, unless you've renamed it to
     * something else. If you have configured your web server to remove this file
     * from your site URIs, set this variable to an empty string.
     */
    public string $indexPage = '';

    /**
     * --------------------------------------------------------------------------
     * URI PROTOCOL
     * --------------------------------------------------------------------------
     *
     * This item determines which server global should be used to retrieve the
     * URI string. The default setting of 'REQUEST_URI' works for most servers.
     * If your links do not seem to work, try one of the other delicious flavors:
     *
     *  'REQUEST_URI': Uses $_SERVER['REQUEST_URI']
     * 'QUERY_STRING': Uses $_SERVER['QUERY_STRING']
     *    'PATH_INFO': Uses $_SERVER['PATH_INFO']
     *
     * WARNING: If you set this to 'PATH_INFO', URIs will always be URL-decoded!
     */
    public string $uriProtocol = 'REQUEST_URI';

    /*
    |--------------------------------------------------------------------------
    | Allowed URL Characters
    |--------------------------------------------------------------------------
    |
    | This lets you specify which characters are permitted within your URLs.
    | When someone tries to submit a URL with disallowed characters they will
    | get a warning message.
    |
    | As a security measure you are STRONGLY encouraged to restrict URLs to
    | as few characters as possible.
    |
    | By default, only these are allowed: `a-z 0-9~%.:_-`
    |
    | Set an empty string to allow all characters -- but only if you are insane.
    |
    | The configured value is actually a regular expression character group
    | and it will be used as: '/\A[<permittedURIChars>]+\z/iu'
    |
    | DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
    |
    */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * --------------------------------------------------------------------------
     * Default Locale
     * --------------------------------------------------------------------------
     *
     * The Locale roughly represents the language and location that your visitor
     * is viewing the site from. It affects the language strings and other
     * strings (like currency markers, numbers, etc), that your program
     * should run under for this request.
     */
    public string $defaultLocale = 'en';

    /**
     * --------------------------------------------------------------------------
     * Negotiate Locale
     * --------------------------------------------------------------------------
     *
     * If true, the current Request object will automatically determine the
     * language to use based on the value of the Accept-Language header.
     *
     * If false, no automatic detection will be performed.
     */
    public bool $negotiateLocale = true;

    /**
     * --------------------------------------------------------------------------
     * Supported Locales
     * --------------------------------------------------------------------------
     *
     * If $negotiateLocale is true, this array lists the locales supported
     * by the application in descending order of priority. If no match is
     * found, the first locale will be used.
     *
     * IncomingRequest::setLocale() also uses this list.
     *
     * @var list<string>
     */
    public array $supportedLocales = [
        'ar-EG',
        'ar-LB',
        'az',
        'bg',
        'bs',
        'ckb',
        'cs',
        'da',
        'de-CH',
        'de-DE',
        'el',
        'en',
        'en-GB',
        'es-ES',
        'es-MX',
        'fa',
        'fr',
        'he',
        'hr-HR',
        'hu',
        'hy',
        'id',
        'it',
        'km',
        'lo',
        'ml',
        'nb',
        'nl-BE',
        'nl-NL',
        'pl',
        'pt-BR',
        'ro',
        'ru',
        'sv',
        'ta',
        'th',
        'tl',
        'tr',
        'uk',
        'ur',
        'vi',
        'zh-Hans',
        'zh-Hant',
    ];

    /**
     * --------------------------------------------------------------------------
     * Application Timezone
     * --------------------------------------------------------------------------
     *
     * The default timezone that will be used in your application to display
     * dates with the date helper, and can be retrieved through app_timezone()
     *
     * @see https://www.php.net/manual/en/timezones.php for list of timezones
     *      supported by PHP.
     */
    public string $appTimezone = 'UTC';

    /**
     * --------------------------------------------------------------------------
     * Default Character Set
     * --------------------------------------------------------------------------
     *
     * This determines which character set is used by default in various methods
     * that require a character set to be provided.
     *
     * @see http://php.net/htmlspecialchars for a list of supported charsets.
     */
    public string $charset = 'UTF-8';

    /**
     * --------------------------------------------------------------------------
     * Force Global Secure Requests
     * --------------------------------------------------------------------------
     *
     * If true, this will force every request made to this application to be
     * made via a secure connection (HTTPS). If the incoming request is not
     * secure, the user will be redirected to a secure version of the page
     * and the HTTP Strict Transport Security (HSTS) header will be set.
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     *
     * If your server is behind a reverse proxy, you must whitelist the proxy
     * IP addresses from which CodeIgniter should trust headers such as
     * X-Forwarded-For or Client-IP in order to properly identify
     * the visitor's IP address.
     *
     * You need to set a proxy IP address or IP address with subnets and
     * the HTTP header for the client IP address.
     *
     * Here are some examples:
     *     [
     *         '10.0.1.200'     => 'X-Forwarded-For',
     *         '192.168.5.0/24' => 'X-Real-IP',
     *     ]
     *
     * For Docker/nginx or same-host deployments where the proxy runs locally,
     * you can set this to ['*'] to trust all proxy sources for forwarded headers.
     * In this case, only allow trusted networks via firewall/security groups.
     *
     * @var array<string, string>
     */
    public array $proxyIPs = [];

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     *
     * Enables the Response's Content Secure Policy to restrict the sources that
     * can be used for images, scripts, CSS files, audio, video, etc. If enabled,
     * the Response object will populate default values for the policy from the
     * `ContentSecurityPolicy.php` file. Controllers can always add to those
     * restrictions at run time.
     *
     * For a better understanding of CSP, see these documents:
     *
     * @see http://www.html5rocks.com/en/tutorials/security/content-security-policy/
     * @see http://www.w3.org/TR/CSP/
     */
    public bool $CSPEnabled = false;

    public function __construct()
    {
        parent::__construct();
        
        // Solution for CodeIgniter 4 limitation: arrays cannot be set from .env
        // See: https://github.com/codeigniter4/CodeIgniter4/issues/7311
        
        // Parse allowedHostnames from .env (comma-separated)
        $envAllowedHostnames = getenv('app.allowedHostnames');
        if ($envAllowedHostnames !== false && trim($envAllowedHostnames) !== '') {
            $this->allowedHostnames = array_values(array_filter(
                array_map('trim', explode(',', $envAllowedHostnames)),
                static fn (string $hostname): bool => $hostname !== ''
            ));
        }
        
        // Parse proxyIPs from .env (comma-separated, supports '*' for all)
        $envProxyIPs = getenv('app.proxyIPs');
        if ($envProxyIPs !== false && trim($envProxyIPs) !== '') {
            $parsed = array_map('trim', explode(',', $envProxyIPs));
            $this->proxyIPs = [];
            foreach ($parsed as $ip) {
                // Support wildcard '*' to trust all proxies
                if ($ip === '*') {
                    $this->proxyIPs['*'] = 'X-Forwarded-For';
                } else {
                    $this->proxyIPs[$ip] = 'X-Forwarded-For';
                }
            }
        }
        
        $this->https_on = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_ENV['FORCE_HTTPS']) && $_ENV['FORCE_HTTPS'] == 'true');

        $host = $this->getValidHost();
        $this->baseURL = $this->https_on ? 'https' : 'http';
        $this->baseURL .= '://' . $host . '/';
        $this->baseURL .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    }

    /**
     * Validates and returns a trusted hostname.
     *
     * Security: Prevents Host Header Injection attacks (GHSA-jchf-7hr6-h4f3)
     * by validating the HTTP_HOST against a whitelist of allowed hostnames.
     *
     * Supports reverse proxies: Checks X-Forwarded-Host header when behind
     * a trusted proxy (configured via $proxyIPs).
     * 
     * IMPORTANT: Both HTTP_HOST and X-Forwarded-Host are validated against
     * the allowedHostnames whitelist. Even if an attacker injects a forged
     * X-Forwarded-Host header, it must match an entry in allowedHostnames.
     * 
     * In production: Fails fast if allowedHostnames is not configured.
     * In development: Allows localhost fallback with an error log.
     * 
     * @return string A validated hostname
     * @throws \RuntimeException If allowedHostnames is not configured in production
     */
    private function getValidHost(): string
    {
        // Get host from forwarded header or HTTP_HOST
        // Both are validated against allowedHostnames whitelist below
        $forwardedHost = $this->getForwardedHost();
        $httpHost = $forwardedHost ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Determine environment
        // CodeIgniter's test bootstrap sets $_SERVER['CI_ENVIRONMENT'] = 'testing'
        // Check $_SERVER first, then $_ENV, then fall back to 'production'
        $environment = $_SERVER['CI_ENVIRONMENT'] ?? $_ENV['CI_ENVIRONMENT'] ?? getenv('CI_ENVIRONMENT') ?: 'production';

        if (empty($this->allowedHostnames)) {
            $errorMessage = 
                'Security: allowedHostnames is not configured. ' .
                'Host header injection protection is disabled. ' .
                'Set app.allowedHostnames in your .env file. ' .
                'Example: app.allowedHostnames = "example.com,www.example.com" ' .
                'Received Host: ' . $httpHost;
            
            // Production: Fail explicitly to prevent silent security vulnerabilities
            // Testing and development: Allow localhost fallback
            if ($environment === 'production') {
                throw new \RuntimeException($errorMessage);
            }
            
            log_message('error', $errorMessage . ' Using localhost fallback (development only).');
            return 'localhost';
        }

        if (in_array($httpHost, $this->allowedHostnames, true)) {
            return $httpHost;
        }

        // Host not in whitelist - use first configured hostname as fallback
        log_message('warning',
            'Security: Rejected HTTP_HOST "' . $httpHost . '" - not in allowedHostnames whitelist. ' .
            'Using fallback: ' . $this->allowedHostnames[0]
        );

        return $this->allowedHostnames[0];
    }

    /**
     * Get the forwarded host from X-Forwarded-Host header when behind a trusted proxy.
     * 
     * When behind a reverse proxy (nginx, load balancer, etc.), the actual hostname
     * is sent in the X-Forwarded-Host header, while HTTP_HOST contains the proxy's hostname.
     * 
     * SECURITY WARNING: The returned hostname is still validated against allowedHostnames
     * whitelist in getValidHost(). This prevents attacks even if an attacker:
     * - Directly accesses PHP-FPM (bypassing nginx)
     * - Forges X-Forwarded-Host with wildcard proxyIPs = '*'
     * - Spoofs the header from an untrusted network
     * 
     * The defense is: allowedHostnames is the authoritative whitelist, regardless
     * of which header the hostname comes from.
     * 
     * @return string|null The forwarded host if configured and behind trusted proxy, null otherwise
     */
    private function getForwardedHost(): ?string
    {
        // Only use forwarded headers if proxyIPs is configured
        if (empty($this->proxyIPs)) {
            return null;
        }

        // Check if trusting all proxies (for Docker/same-host deployments)
        if (isset($this->proxyIPs['*'])) {
            return $_SERVER['HTTP_X_FORWARDED_HOST'] ?? null;
        }

        // Check if the request comes from a trusted proxy
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$this->isTrustedProxy($clientIp)) {
            return null;
        }

        // Return the forwarded host if present
        return $_SERVER['HTTP_X_FORWARDED_HOST'] ?? null;
    }

    /**
     * Check if an IP address is a trusted proxy.
     * 
     * @param string $ip The IP address to check
     * @return bool True if the IP is a trusted proxy
     */
    private function isTrustedProxy(string $ip): bool
    {
        foreach ($this->proxyIPs as $proxyIp => $header) {
            if ($this->ipInRange($ip, $proxyIp)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if an IP address is within a CIDR range.
     * 
     * @param string $ip The IP address to check
     * @param string $range The CIDR range (e.g., '192.168.1.0/24' or '10.0.0.1')
     * @return bool True if the IP is within the range
     */
    private function ipInRange(string $ip, string $range): bool
    {
        // If no subnet mask, check for exact match
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        // Parse CIDR notation
        list($subnet, $bits) = explode('/', $range);
        
        // Convert IP addresses to long format
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        
        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        // Calculate network mask
        $mask = -1 << (32 - (int)$bits);
        $network = $subnetLong & $mask;

        return ($ipLong & $mask) === $network;
    }
}
