<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Configuration;

use ApiGen\Configuration\ConfigurationOptions as CO;
use Nette;
use Nette\Utils\Strings;


/**
 * @method Configuration onOptionsResolve(array $config)
 */
class Configuration extends Nette\Object
{

	const GROUPS_AUTO = 'auto';
	const GROUPS_NAMESPACES = 'namespaces';
	const GROUPS_PACKAGES = 'packages';

	/**
	 * @var array
	 */
	public $onOptionsResolve = [];

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @var ConfigurationOptionsResolver
	 */
	private $configurationOptionsResolver;


	public function __construct(ConfigurationOptionsResolver $configurationOptionsResolver)
	{
		$this->configurationOptionsResolver = $configurationOptionsResolver;
	}


	/**
	 * @return array
	 */
	public function resolveOptions(array $options)
	{
		$options = $this->unsetConsoleOptions($options);
		$this->options = $options = $this->configurationOptionsResolver->resolve($options);
		$this->onOptionsResolve($options);
		return $options;
	}


	/**
	 * @param string $name
	 * @return mixed|NULL
	 */
	public function getOption($name)
	{
		if (isset($this->getOptions()[$name])) {
			return $this->getOptions()[$name];
		}
		return NULL;
	}


	/**
	 * @return array
	 */
	public function getOptions()
	{
		if ($this->options === NULL) {
			$this->resolveOptions([]);
		}
		return $this->options;
	}


	/**
	 * @param int $namespaceCount
	 * @param int $packageCount
	 * @return bool
	 */
	public function areNamespacesEnabled($namespaceCount, $packageCount)
	{
		if ($this->getOption(CO::GROUPS) === self::GROUPS_NAMESPACES) {
			return TRUE;
		}
		if ($this->getOption(CO::GROUPS) === self::GROUPS_AUTO && ($namespaceCount > 0 || $packageCount === 0)) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @param bool $areNamespacesEnabled
	 * @return bool
	 */
	public function arePackagesEnabled($areNamespacesEnabled)
	{
		if ($this->getOption(CO::GROUPS) === self::GROUPS_PACKAGES) {
			return TRUE;

		} elseif ($this->getOption(CO::GROUPS) === self::GROUPS_AUTO && ($areNamespacesEnabled === FALSE)) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @return string
	 */
	public function getZipFileName()
	{
		$webalizedTitle = Strings::webalize($this->getOption(CO::TITLE), NULL, FALSE);
		return ($webalizedTitle ? '-' : '') . 'API-documentation.zip';
	}


	/**
	 * @return array
	 */
	private function unsetConsoleOptions(array $options)
	{
		unset($options[CO::CONFIG], $options['help'], $options['version'], $options['quiet']);
		return $options;
	}

}
