<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Security extends CI_Security
{
    /**
     * CSRF Set Cookie with samesite
     *
     * @codeCoverageIgnore
     * @return  CI_Security
     */
    public function csrf_set_cookie()
    {
        $expire = time() + $this->_csrf_expire;
        $secure_cookie = (bool)config_item('cookie_secure');

        if ($secure_cookie && !is_https())
        {
            return FALSE;
        }

        setcookie($this->_csrf_cookie_name,
            $this->_csrf_hash,
            ['samesite' => 'Strict',
                'secure' => $secure_cookie,
                'expires' => $expire,
                'path' => config_item('cookie_path'),
                'domain' => config_item('cookie_domain'),
                'httponly' => FALSE]);

        log_message('info', 'CSRF cookie sent');

        return $this;
    }
}
