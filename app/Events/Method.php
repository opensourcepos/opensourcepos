<?php

namespace App\Events;

class Method
{
    public static function validate_method(): void
    {
        $url = rawurldecode($_SERVER['REQUEST_URI'] ?? '');

        $postRequired = preg_match('/\/(save|delete|remove|logout)(\/|$|\?)/i', $url);

        if ($postRequired && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' && empty($_POST)) {
            echo 'Method not allowed';

            exit;
        }
    }
}
