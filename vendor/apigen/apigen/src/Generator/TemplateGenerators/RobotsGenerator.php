<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator\TemplateGenerators;

use ApiGen\Configuration\Configuration;
use ApiGen\Configuration\ConfigurationOptions as CO;
use ApiGen\Configuration\Theme\ThemeConfigOptions as TCO;
use ApiGen\Generator\ConditionalTemplateGenerator;
use ApiGen\Templating\TemplateFactory;


class RobotsGenerator implements ConditionalTemplateGenerator
{

	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var TemplateFactory
	 */
	private $templateFactory;


	public function __construct(Configuration $configuration, TemplateFactory $templateFactory)
	{
		$this->configuration = $configuration;
		$this->templateFactory = $templateFactory;
	}


	public function generate()
	{
		$this->templateFactory->createForType(TCO::ROBOTS)
			->save();
	}


	/**
	 * @return bool
	 */
	public function isAllowed()
	{
		return (bool) $this->configuration->getOption(CO::BASE_URL);
	}

}
