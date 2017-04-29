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


class OpensearchGenerator implements ConditionalTemplateGenerator
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
		$this->templateFactory->createForType(TCO::OPENSEARCH)
			->save();
	}


	/**
	 * @return bool
	 */
	public function isAllowed()
	{
		$options = $this->configuration->getOptions();
		return $options[CO::GOOGLE_CSE_ID] && $options[CO::BASE_URL];
	}

}
