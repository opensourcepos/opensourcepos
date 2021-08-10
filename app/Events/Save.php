<?php

namespace App\Events;

class Save
{
	public static function validate_save()
	{
		$url = $_SERVER['REQUEST_URI'];

		$post_required = preg_match('/\/(logout|save|delete*|remove*)\/?\d*?/', $url);

		if($post_required && $_SERVER["REQUEST_METHOD"] != "POST" && empty($_POST))
		{
			echo "Method not allowed";
			die;
		}
	}
}
