<?php

namespace Config;

use App\Models\Appconfig;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Config\BaseConfig;

/**
 * This class holds the configuration options stored from the database so that on launch those settings can be cached
 * once in memory.  The settings are referenced frequently, so there is a significant performance hit to not storing
 * them.
 */
class OSPOS extends BaseConfig
{
	public array $settings;
	public string $commit_sha1 = 'dev';	//TODO: Travis scripts need to be updated to replace this with the commit hash on build
	private CacheInterface $cache;

	public function __construct()
	{
		parent::__construct();
		$this->cache = Services::cache();
		$this->set_settings();
	}

	/**
	 * @return void
	 */
	public function set_settings(): void
	{
		$cache = $this->cache->get('settings');

		if($cache)
		{
			$this->settings = decode_array($cache);
		}
		else
		{
			$appconfig = model(Appconfig::class);
			foreach($appconfig->get_all()->getResult() as $app_config)
			{
				$this->settings[$app_config->key] = $app_config->value;
			}
			$this->cache->save('settings', encode_array($this->settings));
		}
	}

	/**
	 * @return void
	 */
	public function update_settings(): void
	{
		$this->cache->delete('settings');
		$this->set_settings();
	}
}
