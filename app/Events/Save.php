<?php

namespace App\Events;

class Save
{
	public static function validate_save()
	{
		$url = $_SERVER['REQUEST_URI'];
		$is_save = preg_match('/save\/\d*?/', $url);

		if($is_save && $_SERVER["REQUEST_METHOD"] != "POST" && empty($_POST))
		{
			echo "Method not allowed";
			die;
		}
	}
}
