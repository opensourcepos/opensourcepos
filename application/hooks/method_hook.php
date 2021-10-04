<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function validate_method()
{

    $url = $_SERVER['REQUEST_URI'];

    $post_required = preg_match('/(save|delete*|remove*)\/?\d*?/', $url);

    if($post_required && $_SERVER["REQUEST_METHOD"] != "POST" && empty($_POST))
    {
        echo "Method not allowed";
        die;
    }

}
