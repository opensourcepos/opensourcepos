<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Rate limits login/migrate POST attempts by IP and submitted username to
 * mitigate brute-force/credential-stuffing. Backed by CodeIgniter's
 * cache-based Throttler, so limits are per-server (not shared on file cache).
 *
 * Tunable via `throttle.capacity` (default 5; 0 disables) and
 * `throttle.seconds` (window, default 60) in .env.
 */
class Throttle implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if ($request->getMethod() !== 'POST') {
            return null;
        }

        // Non-positive integer = explicit disable; missing/non-numeric falls
        // back to the default so a typo (e.g. "five") cannot bypass lockout.
        $capacity = filter_var(env('throttle.capacity'), FILTER_VALIDATE_INT);
        if ($capacity === false) {
            $capacity = 5;
        }
        if ($capacity <= 0) {
            return null;
        }

        $seconds = filter_var(env('throttle.seconds'), FILTER_VALIDATE_INT);
        if ($seconds === false) {
            $seconds = 60;
        }
        $seconds = max(1, $seconds);

        helper('security');

        $throttler = Services::throttler();
        $secret    = checkThrottleEncryption();

        $ipKey       = 'login-ip-' . hash_hmac('sha256', $request->getIPAddress(), $secret);
        $rawUsername = $request->getPost('username');
        $username    = is_scalar($rawUsername) ? strtolower((string) $rawUsername) : '';
        $usernameKey = $username !== '' ? 'login-user-' . hash_hmac('sha256', $username, $secret) : null;

        $ipOk       = $throttler->check($ipKey, $capacity, $seconds);
        $usernameOk = $usernameKey === null || $throttler->check($usernameKey, $capacity, $seconds);

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

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
