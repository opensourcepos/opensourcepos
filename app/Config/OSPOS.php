<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * This class holds the configuration options stored from the database so that on launch those settings can be cached
 * once in memory.  The settings are referenced frequently, so there is a significant performance hit to not storing
 * them.
 */
class OSPOS extends BaseConfig
{
	public $settings = [];
}