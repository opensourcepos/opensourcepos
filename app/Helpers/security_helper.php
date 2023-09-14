<?php

use CodeIgniter\Encryption\Encryption;


/**
 * @return bool
 */
function check_encryption(): bool
{
	$old_key = config('Encryption')->key;

	if((empty($old_key)) or (strlen($old_key) < 64))
	{
		//Create Key
		$encryption = new Encryption();
		$key = bin2hex($encryption->createKey());
		config('Encryption')->key = $key;

		//Write to .env
		//For security reasons. at some sites the web server may not have write
		//access to the base url, so we must not try to create files there.
		//This code attempts to make just .env writable so it can be updated.
		//A backup copy is written to the writable/backup directory.
		//To make this code work, create the backup directory as below and
		//ensure it is writeable by the webserver, including setting
		//any SELinux permissions needed under eg Fedora
		//We log a message to the user telling them what has happened.

		$config_path = ROOTPATH . '.env';
		$new_config_path = WRITEPATH . '/backup/.env' ;
		$backup_path = WRITEPATH . '/backup/.env.bak';

		//Copy to backup
		if(!copy($config_path, $backup_path))
		{
			log_message('error', "Unable to copy $config_path to $backup_path");
			//we could return false at this point,
			//but better for business to try the update first
		}

		//Attempt to make these files writable
		//by the webserver user and group
		//even if the directory is not writable
		@chmod($config_path, 0770);
		@chmod($backup_path, 0770);
		
		$config_file = file_get_contents($config_path);
		$config_file = preg_replace("/(encryption\.key.*=.*)('.*')/", "$1'$key'", $config_file);

		if(!empty($old_key))
		{
			
			$old_line = "# encryption.key = '$old_key' REMOVE IF UNNEEDED\r\n";
			$insertion_point = stripos($config_file, 'encryption.key');
			$config_file = substr_replace($config_file, $old_line, $insertion_point,0);
		}

		//Update the master copy
		$handle = @fopen($config_path, 'w+');
		
		//Cope with failure to open the handle
		if(empty($handle))
		{
			log_message('error', "Unable to open $config_path for updating");
			return false;
		}
		else 
		{	
			@chmod($config_path, 0770);
			fwrite($handle, $config_file) === FALSE;
			fclose($handle);

			log_message('info', "File $config_path has been updated."); 
		}
	}

	return true;
}

function abort_encryption_conversion()
{
	$config_path = ROOTPATH . '.env';
	$backup_path = WRITEPATH . '/backup/.env.bak';

	$config_file = file_get_contents($backup_path);

	//Update the master copy
	$handle = @fopen($config_path, 'w+');
	
	//Cope with failure to open the handle
	if(empty($handle))
	{
		log_message('error', "Unable to open $config_path for updating");
	}
	else 
	{	
		@chmod($config_path, 0770);
		fwrite($handle, $config_file) === FALSE;
		fclose($handle);

		log_message('info', "File $config_path has been updated."); 
	}
}
