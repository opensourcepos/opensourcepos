<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Configuration\Theme;

use ApiGen\Configuration\Exceptions\ConfigurationException;
use ApiGen\Configuration\Readers\NeonFile;


class ThemeConfig
{

	/**
	 * @var mixed[]
	 */
	private $options;

	/**
	 * @var string
	 */
	private $filePath;

	/**
	 * @var ThemeConfigOptionsResolver
	 */
	private $themeConfigOptionsResolver;


	/**
	 * @param string $filePath
	 */
	public function __construct($filePath, ThemeConfigOptionsResolver $themeConfigOptionsResolver)
	{
		if ( ! is_file($filePath)) {
			throw new ConfigurationException("File $filePath doesn't exist");
		}
		$this->filePath = $filePath;
		$this->themeConfigOptionsResolver = $themeConfigOptionsResolver;
	}


	/**
	 * @return mixed[]
	 */
	public function getOptions()
	{
		if ($this->options === NULL) {
			$file = new NeonFile($this->filePath);
			$values = $file->read();
			$values['templatesPath'] = dirname($this->filePath);
			$this->options = $this->themeConfigOptionsResolver->resolve($values);
		}
		return $this->options;
	}

}
