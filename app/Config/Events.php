<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', function () /* use ($config_path) */ {  //TODO -> Undefined variable
	if (ENVIRONMENT !== 'testing') {
		if (ini_get('zlib.output_compression')) {
			throw FrameworkException::forEnabledZlibOutputCompression();
		}

		while (ob_get_level() > 0) {
			ob_end_flush();
		}

		ob_start(function ($buffer) {
			return $buffer;
		});

		try {
			// $dotenv = new Dotenv\Dotenv($config_path);
			// $dotenv->overload(); //TODO Trows errors -> Dotenv
		} catch (Exception $e) {
			// continue, .env file not present
		}
	}

	/*
	 * --------------------------------------------------------------------
	 * Debug Toolbar Listeners.
	 * --------------------------------------------------------------------
	 * If you delete, they will no longer be collected.
	 */
	if (CI_DEBUG && !is_cli()) {
		Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
		Services::toolbar()->respond();
	}
});

Events::on('post_controller_constructor', ['Load_config', 'load_config']);

Events::on('post_controller', ['Db_log', 'db_log_queries']);

Events::on('pre_controller', ['Save', 'validate_save']);
