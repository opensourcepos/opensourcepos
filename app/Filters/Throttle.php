<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Rate limits login/migrate POST attempts, keyed by IP and by submitted
 * username, to mitigate brute-force and credential-stuffing attacks
 * (GHSA-hm9c-xchj-xgcp). Backed by CodeIgniter's cache-based Throttler,
 * so limits are per-server (not shared across nodes on file cache).
 */
class Throttle implements FilterInterface
{
    private const CAPACITY = 5;
    private const SECONDS  = 60;

    public function before(RequestInterface $request, $arguments = null)
    {
        if ($request->getMethod() !== 'POST') {
            return;
        }

        $throttler = Services::throttler();

        $ipKey       = 'login-ip-' . $request->getIPAddress();
        $username    = strtolower((string) $request->getPost('username'));
        $usernameKey = $username !== '' ? 'login-user-' . $username : null;

        $ipOk       = $throttler->check($ipKey, self::CAPACITY, self::SECONDS);
        $usernameOk = $usernameKey === null || $throttler->check($usernameKey, self::CAPACITY, self::SECONDS);

        if (!$ipOk || !$usernameOk) {
            log_message('warning', 'Login throttled for IP {ip} (username: {username})', [
                'ip'       => $request->getIPAddress(),
                'username' => $username !== '' ? $username : '(none)',
            ]);

            return service('response')
                ->setStatusCode(429)
                ->setJSON([
                    'success' => false,
                    'message' => lang('Login.too_many_attempts'),
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
