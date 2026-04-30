<?php

use CodeIgniter\Events\Events;

if (!function_exists('pluginContent')) {
    function pluginContent(string $section, array $data = []): string
    {
        ob_start();
        Events::trigger("view:{$section}", $data);
        return ob_get_clean() ?: '';
    }
}

if (!function_exists('pluginContentExists')) {
    function pluginContentExists(string $section): bool
    {
        return !empty(Events::listeners("view:{$section}"));
    }
}
