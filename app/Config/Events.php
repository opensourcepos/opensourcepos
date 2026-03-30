<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;
use App\Events\Db_log;
use App\Events\Load_config;
use App\Events\Method;
use App\Libraries\Plugins\PluginManager;

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
    
    $pluginManager = new PluginManager();
    $pluginManager->discoverPlugins();
    $pluginManager->registerPluginEvents();
});

$config = new Load_config();
Events::on('post_controller_constructor', [$config, 'load_config']);

$db_log = new Db_log();
Events::on('DBQuery', [$db_log, 'db_log_queries']);

$method = new Method();
Events::on('pre_controller', [$method, 'validate_method']);