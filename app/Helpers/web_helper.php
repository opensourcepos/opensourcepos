<?php

if (!function_exists('base64url_encode')) {
    /**
     * Encode data to Base64 URL-safe string.
     *
     * @param string $data
     * @return string
     */
    function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    /**
     * Decode Base64 URL-safe string to original data.
     *
     * @param string $data
     * @return string|false
     */
    function base64url_decode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}