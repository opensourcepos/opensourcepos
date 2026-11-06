<?php

namespace Config;

use CodeIgniter\Settings\Config\Settings as BaseSettings;

class Settings extends BaseSettings
{
    /**
     * OSPOS has no ospos_settings table and stores its own configuration
     * in ospos_app_config, so fall back to reading values directly from
     * config classes instead of a database-backed handler.
     */
    public $handlers = ['array'];
}
