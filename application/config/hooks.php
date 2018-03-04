<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
$hook['post_controller_constructor'][] = array(
                                    'class'    => '',
                                    'function' => 'load_config',
                                    'filename' => 'load_config.php',
                                    'filepath' => 'hooks'
                                );

// 'post_controller' indicated execution of hooks after controller is finished
$hook['post_controller'] = array(
                                    'class' => '',
                                    'function' => 'db_log_queries',
                                    'filename' => 'db_log.php',
                                    'filepath' => 'hooks'
                                );

$hook['pre_system'] = function() {
    $config_path = APPPATH . 'config/';
	try {
		$dotenv = new Dotenv\Dotenv($config_path);
		$dotenv->overload();
	} catch(Exception $e) {
		// continue, .env file not present
	}
};
