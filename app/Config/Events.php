<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;
use App\Events\Db_log;
use App\Events\Load_config;
use App\Events\Method;

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

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

$config = new Load_config();
Events::on('post_controller_constructor', [$config, 'load_config']);

$db_log = new Db_log();
Events::on('DBQuery', [$db_log, 'db_log_queries']);

$method = new Method();
Events::on('pre_controller', [$method, 'validate_method']);

/**
 * This event triggered when an item sale occurs.
 * Plugin functionality is triggered here.
 */
Events::on('item_sale', static function (array $saleData): void {
    // Call plugin controller methods to handle the item sale data sent in the static function.
    log_message('debug', "Item sale event triggered on sale ID: {$saleData['sale_id_num']}");
});

/**
 * This event triggered when an item return occurs.
 * Plugin functionality is triggered here.
 */
Events::on('item_return', static function (): void {
    // Call plugin controller methods to handle the item return data sent in the static function.
});

/**
 * This event triggered when an item is changed. This can be an item create, update or delete.
 * Plugin functionality is triggered here.
 */
Events::on('item_change', static function (int $itemId): void {
    // Call plugin controller methods to handle the item change data sent in the static function.
    log_message('debug', "Item change event triggered on item ID: $itemId");
});

/**
 * This event triggered when an item inventory action occurs.
 * This can be during a receiving action or the update inventory partial view in items.
 */
Events::on('item_inventory', static function (): void {
    // Call plugin controller methods to handle the item inventory data sent in the static function.
});

/**
 * This event triggered when an items CSV import occurs.
 * Plugin functionality is triggered here.
 */
Events::on('items_csv_import', static function (): void {
    // Call plugin controller methods to handle the items CSV import data sent in the static function.
});

/**
 * This event triggered when a customers CSV import occurs.
 * Plugin functionality is triggered here.
 */
Events::on('customers_csv_import', static function (): void {
    // Call plugin controller methods to handle the customers CSV import data sent in the static function.
});
