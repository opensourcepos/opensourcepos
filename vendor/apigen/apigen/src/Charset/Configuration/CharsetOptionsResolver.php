<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Charset\Configuration;

use ApiGen\Charset\Encoding;
use ApiGen\Configuration\OptionsResolverFactory;
use Nette;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CharsetOptionsResolver extends Nette\Object
{

	const CHARSETS = 'charsets';

	/**
	 * @var OptionsResolverFactory
	 */
	private $optionsResolverFactory;

	/**
	 * @var OptionsResolver
	 */
	private $resolver;


	public function __construct(OptionsResolverFactory $optionsResolverFactory)
	{
		$this->optionsResolverFactory = $optionsResolverFactory;
	}


	/**
	 * @return string[]
	 */
	public function getDefaults()
	{
		return [self::CHARSETS => [Encoding::UTF_8]];
	}


	/**
	 * @param string[] $options
	 * @return string[]
	 */
	public function resolve(array $options = [])
	{
		$this->resolver = $this->optionsResolverFactory->create();
		$this->setDefaults();
		$this->setNormalizers();
		$options = $this->normalizeInput($options);
		$options = $this->resolver->resolve($options);
		return $this->normalizeOutput($options);
	}


	private function setDefaults()
	{
		$this->resolver->setDefaults($this->getDefaults());
	}


	private function setNormalizers()
	{
		$this->resolver->setNormalizers([
			self::CHARSETS => function (Options $options, $value) {
				$value = array_map('strtoupper', $value);
				$value = $this->replaceWin1250WithIso($value);
				$value = $this->filterSupportedCharsets($value);
				$value = $this->moveUtfFirst($value);
				return $value;
			}
		]);
	}


	/**
	 * @return array
	 */
	private function replaceWin1250WithIso(array $charsets)
	{
		if (($key = array_search(Encoding::WIN_1250, $charsets)) !== FALSE) {
			$charsets[$key] = Encoding::ISO_8859_1;
		}
		return $charsets;
	}


	/**
	 * @return array
	 */
	private function filterSupportedCharsets(array $charsets)
	{
		$supportedEncodingList = array_map('strtoupper', mb_list_encodings());
		return array_intersect($charsets, $supportedEncodingList);
	}


	/**
	 * @return array
	 */
	private function moveUtfFirst(array $charsets)
	{
		if (($key = array_search(Encoding::UTF_8, $charsets)) !== FALSE) {
			unset($charsets[$key]);
		}
		array_unshift($charsets, Encoding::UTF_8);
		return $charsets;
	}


	/**
	 * @return array
	 */
	private function normalizeInput(array $options)
	{
		if (isset($options[self::CHARSETS])) {
			return $options;
		}
		return [self::CHARSETS => $options];
	}


	/**
	 * @return string[]
	 */
	private function normalizeOutput(array $options)
	{
		return $options[self::CHARSETS];
	}

}
