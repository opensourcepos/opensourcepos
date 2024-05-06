<?php

use CodeIgniter\Encryption\Encryption;

/**
 * @return bool
 */
function check_encryption(): bool
{
	$old_key = config('Encryption')->key;

	if((empty($old_key)) || (strlen($old_key) < 64))
	{
		//Create Key
		$encryption = new Encryption();
		$key = bin2hex($encryption->createKey());
		config('Encryption')->key = $key;

		//Write to .env
		$config_path = ROOTPATH . '.env';
		$new_config_path = WRITEPATH . '/backup/.env' ;
		$backup_path = WRITEPATH . '/backup/.env.bak';

		$backup_folder = WRITEPATH . '/backup';

		if (!file_exists($backup_folder) && !mkdir($backup_folder))
		{
			log_message('error', 'Could not create backup folder');
			return false;
		}

		if(!copy($config_path, $backup_path))
		{
			log_message('error', "Unable to copy $config_path to $backup_path");
		}

		//Copy to backup
		@chmod($config_path, 0660);
		@chmod($backup_path, 0660);

		$config_file = file_get_contents($config_path);
		$config_file = preg_replace("/(encryption\.key.*=.*)('.*')/", "$1'$key'", $config_file);

		if(!empty($old_key))
		{
			$old_line = "# encryption.key = '$old_key' REMOVE IF UNNEEDED\r\n";
			$insertion_point = stripos($config_file, 'encryption.key');
			$config_file = substr_replace($config_file, $old_line, $insertion_point,0);
		}

		$handle = @fopen($config_path, 'w+');

		if(empty($handle))
		{
			log_message('error', "Unable to open $config_path for updating");
			return false;
		}

		@chmod($config_path, 0660);
		$write_failed = !fwrite($handle, $config_file);
		fclose($handle);

		if($write_failed)
		{
			log_message('error', "Unable to write to $config_path for updating.");
			return false;
		}
		log_message('info', "File $config_path has been updated.");
	}

	return true;
}

/**
 * @return void
 */
function abort_encryption_conversion(): void
{
	$config_path = ROOTPATH . '.env';
	$backup_path = WRITEPATH . '/backup/.env.bak';

	$config_file = file_get_contents($backup_path);

	$handle = @fopen($config_path, 'w+');

	if(empty($handle))
	{
		log_message('error', "Unable to open $config_path to undo encryption conversion");
	}
	else
	{
		@chmod($config_path, 0660);
		$write_failed = !fwrite($handle, $config_file);
		fclose($handle);

		if($write_failed)
		{
			log_message('error', "Unable to write to $config_path to undo encryption conversion.");
			return;
		}
		log_message('info', "File $config_path has been updated to undo encryption conversion");
	}
}

/**
 * @return void
 */
function remove_backup(): void
{
	$backup_path = WRITEPATH . '/backup/.env.bak';
	if( ! file_exists($backup_path))
	{
		return;
	}
	if(!unlink($backup_path))
	{
		log_message('error', "Unable to remove $backup_path.");
		return;
	}
	log_message('info', "File $backup_path has been removed");
}
