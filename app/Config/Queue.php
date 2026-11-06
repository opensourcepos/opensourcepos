<?php

namespace Config;

use App\Jobs\CustomerImportJob;
use App\Jobs\ItemImportJob;
use CodeIgniter\Queue\Config\Queue as BaseQueue;

/**
 * Core queue names: 'default', 'imports', 'api'.
 *
 * 'plugin.{pluginId}' is reserved for the plugin system (see issue #3833
 * Phase 2 / PR #4407) — do not push to a 'plugin.*' queue until the plugin
 * infrastructure (PluginManager, BasePluginJob) lands.
 */
class Queue extends BaseQueue
{
    public string $defaultHandler = 'database';

    public array $jobHandlers = [
        'item_import'     => ItemImportJob::class,
        'customer_import' => CustomerImportJob::class,
    ];

    /**
     * Priority convention (issue #3833 Phase 2): 'normal' is the default for
     * all jobs; 'high' is for user-facing/time-sensitive work; 'low' is for
     * bulk background processing and large imports.
     */
    public array $queueDefaultPriority = [
        'default' => 'normal',
        'imports' => 'normal',
        'api'     => 'normal',
    ];

    public array $queuePriorities = [
        'default' => ['high', 'normal', 'low'],
        'imports' => ['high', 'normal', 'low'],
        'api'     => ['high', 'normal', 'low'],
    ];
}
