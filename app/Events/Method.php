<?php

namespace App\Events;

class Method
{
    /**
     * @return void
     */
    public static function validate_method(): void
    {
        $url = $_SERVER['REQUEST_URI'];

        $post_required = preg_match('/\/(save|delete*|remove*)\/?\d*?/', $url);

        if ($post_required && $_SERVER["REQUEST_METHOD"] != "POST" && empty($_POST)) {
            echo "Method not allowed";
            die;
        }
    }
}
