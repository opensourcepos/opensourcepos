<?php

use CodeIgniter\Events\Events;

if (!function_exists('log_plugin_message')) {
    function log_plugin_message(string $pluginId, string $level, string $message, ?string $logName = null): void
    {
        service('pluginLogger')->log($pluginId, $level, $message, $logName);
    }
}

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
