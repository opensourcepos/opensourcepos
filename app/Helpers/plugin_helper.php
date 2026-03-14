<?php

use CodeIgniter\Events\Events;

if (!function_exists('plugin_content'))
{
    function plugin_content(string $section, array $data = []): string
    {
        $results = Events::trigger("view:{$section}", $data);
        
        if (is_array($results))
        {
            return implode('', array_filter($results, fn($r) => is_string($r)));
        }
        
        return is_string($results) ? $results : '';
    }
}

if (!function_exists('plugin_content_exists'))
{
    function plugin_content_exists(string $section): bool
    {
        $observers = Events::listRegistered("view:{$section}");
        return !empty($observers);
    }
}