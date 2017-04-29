<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Templating\Filters;

use ApiGen;
use ApiGen\Configuration\Configuration;
use ApiGen\Configuration\ConfigurationOptions as CO;
use Nette;


class AnnotationFilters extends Filters
{

	/**
	 * @var array
	 */
	private $rename = [
		'usedby' => 'used by'
	];

	/**
	 * @var string[]
	 */
	private $remove = [
		'package', 'subpackage', 'property', 'property-read', 'property-write', 'method', 'abstract', 'access',
		'final', 'filesource', 'global', 'name', 'static', 'staticvar'
	];

	/**
	 * @var array
	 */
	private $order = [
		'deprecated' => 0,
		'category' => 1,
		'copyright' => 2,
		'license' => 3,
		'author' => 4,
		'version' => 5,
		'since' => 6,
		'see' => 7,
		'uses' => 8,
		'usedby' => 9,
		'link' => 10,
		'internal' => 11,
		'example' => 12,
		'tutorial' => 13,
		'todo' => 14
	];

	/**
	 * @var Configuration
	 */
	private $configuration;


	public function __construct(Configuration $configuration)
	{
		$this->configuration = $configuration;
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function annotationBeautify($name)
	{
		if (isset($this->rename[$name])) {
			$name = $this->rename[$name];
		}
		return Nette\Utils\Strings::firstUpper($name);
	}


	/**
	 * @return array
	 */
	public function annotationFilter(array $annotations, array $customToRemove = [])
	{
		$annotations = $this->filterOut($annotations, $this->remove);
		$annotations = $this->filterOut($annotations, $customToRemove);

		if ( ! $this->configuration->getOption(CO::INTERNAL)) {
			unset($annotations['internal']);
		}

		if ( ! $this->configuration->getOption(CO::TODO)) {
			unset($annotations['todo']);
		}

		return $annotations;
	}


	/**
	 * @return array
	 */
	public function annotationSort(array $annotations)
	{
		uksort($annotations, function ($one, $two) {
			if (isset($this->order[$one], $this->order[$two])) {
				return $this->order[$one] - $this->order[$two];

			} elseif (isset($this->order[$one])) {
				return -1;

			} elseif (isset($this->order[$two])) {
				return 1;

			} else {
				return strcasecmp($one, $two);
			}
		});

		return $annotations;
	}


	/**
	 * @return array
	 */
	private function filterOut(array $annotations, array $toRemove)
	{
		foreach ($toRemove as $annotation) {
			unset($annotations[$annotation]);
		}
		return $annotations;
	}

}
