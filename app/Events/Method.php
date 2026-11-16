<?php

namespace App\Events;

class Method
{
    public static function validate_method(): void
    {
        [$requestPath] = explode('?', $_SERVER['REQUEST_URI'] ?? '', 2);
        $path = rawurldecode($requestPath);

        $postRequired = preg_match('/\/(save|delete|remove|logout)(\/|$)/i', $path);

        if ($postRequired && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' && empty($_POST)) {
            echo 'Method not allowed';

            exit;
        }
    }
}
