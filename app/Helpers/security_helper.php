<?php

use CodeIgniter\Encryption\Encryption;


/**
 * @return bool
 */
function check_encryption(): bool
{
	$old_key = config('Encryption')->key;

	if(strlen($old_key) < 64)
	{
		//Create Key
		$encryption = new Encryption();
		$key = bin2hex($encryption->createKey());
		config('Encryption')->key = $key;

		//Write to .env
		//For security reasons. the web server must not have write
		//access to the base url, so we must not try to write or update files there.
		//To make this code work, create the backup directory as below and
		//ensure it is writeable by the webserver, including setting
		//any SELinux permissions needed under eg Fedora
		//We present a message to the user telling them to
		//copy the .env file into place manually.
		$config_path = ROOTPATH . '.env';
		$backup_path = ROOTPATH . '/writable/backup/.env.bak';

		copy($config_path, $backup_path);
		@chmod($backup_path, 0770);
		$config_file = file_get_contents($config_path);

		$config_file = preg_replace("/(encryption\.key.*=.*)('.*')/", "$1'$key'", $config_file);

		if(!empty($old_key))
		{
			$old_line = "# encryption.key = '$old_key' REMOVE IF UNNEEDED\r\n";
			$insertion_point = stripos($config_file, 'encryption.key');
			$config_file = substr_replace($config_file, $old_line, $insertion_point,0);
		}

		$new_config_path = ROOTPATH . '/writable/backup/.env' ;

		//Changes to .env must only be invoked by Login controller
		$router = service('router');
		$controller = $router->controllerName();
		if(strtolower(class_basename($controller)) == 'login')
		{
			$handle = @fopen($new_config_path, 'w+') or die("Unable to open " . $new_config_path . " for writing");
			@chmod($new_config_path, 0770);
			fwrite($handle, $config_file) === FALSE;
			fclose($handle);
			echo '<script type="text/javascript">';
			echo ' alert("File ' . $new_config_path . ' has been created. You must move this to ' . ROOTPATH . '")'; 
			echo '</script>';
		}
	}

	return true;
}

function abort_encryption_conversion()
{
	$new_config_path = ROOTPATH . '/writable/backup/.env' ;
	$backup_path = ROOTPATH . '/writable/backup/.env.bak';

	unlink($new_config_path);
	rename($backup_path, $new_config_path);
}
