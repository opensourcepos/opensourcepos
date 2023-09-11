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
		$config_path = ROOTPATH . '.env';
		$backup_path = $config_path . '.bak';

		copy($config_path, $backup_path);
		$config_file = file_get_contents($config_path);
		@chmod($config_path, 0440);

		$config_file = preg_replace("/(encryption\.key.*=.*)('.*')/", "$1'$key'", $config_file);

		if(!empty($old_key))
		{
			$old_line = "# encryption.key = '$old_key' REMOVE IF UNNEEDED\r\n";
			$insertion_point = stripos($config_file, 'encryption.key');
			$config_file = substr_replace($config_file, $old_line, $insertion_point,0);
		}

		@chmod($config_path, 0770);

		if(is_writable($config_path))
		{
			// Write the new config.php file
			$handle = @fopen($config_path, 'w+');
			fwrite($handle, $config_file) === FALSE;
			fclose($handle);
		}
		else
		{
			return false;
		}
	}

	return true;
}

function abort_encryption_conversion()
{
	$config_path = ROOTPATH . '.env';
	$backup_path = $config_path . '.bak';

	unlink($config_path);
	rename($backup_path, $config_path);
}
