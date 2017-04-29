<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Reflection;

use ApiGen\Configuration\ConfigurationOptions as CO;
use TokenReflection;
use TokenReflection\Exception\BaseException;
use TokenReflection\ReflectionAnnotation;
use TokenReflection\ReflectionClass;
use TokenReflection\ReflectionConstant;
use TokenReflection\ReflectionFunction;


abstract class ReflectionElement extends ReflectionBase
{

	/**
	 * @var bool
	 */
	protected $isDocumented;

	/**
	 * @var array
	 */
	protected $annotations;

	/**
	 * Reasons why this element's reflection is invalid.
	 *
	 * @var array
	 */
	private $reasons = [];


	/**
	 * @return ReflectionExtension|NULL
	 */
	public function getExtension()
	{
		$extension = $this->reflection->getExtension();
		return $extension === NULL ? NULL : $this->reflectionFactory->createFromReflection($extension);
	}


	/**
	 * @return bool
	 */
	public function getExtensionName()
	{
		return $this->reflection->getExtensionName();
	}


	/**
	 * @return int
	 */
	public function getStartPosition()
	{
		return $this->reflection->getStartPosition();
	}


	/**
	 * @return int
	 */
	public function getEndPosition()
	{
		return $this->reflection->getEndPosition();
	}


	/**
	 * @return bool
	 */
	public function isMain()
	{
		$main = $this->configuration->getOption(CO::MAIN);
		return empty($main) || strpos($this->getName(), $main) === 0;
	}


	/**
	 * @return bool
	 */
	public function isDocumented()
	{
		if ($this->isDocumented === NULL) {
			$this->isDocumented = $this->reflection->isTokenized() || $this->reflection->isInternal();

			if ($this->isDocumented) {
				$php = $this->configuration->getOption(CO::PHP);
				$internal = $this->configuration->getOption(CO::INTERNAL);

				if ( ! $php && $this->reflection->isInternal()) {
					$this->isDocumented = FALSE;

				} elseif ( ! $internal && ($internal = $this->reflection->getAnnotation('internal'))
					&& empty($internal[0])
				) {
					$this->isDocumented = FALSE;

				} elseif ($this->reflection->hasAnnotation('ignore')) {
					$this->isDocumented = FALSE;
				}
			}
		}

		return $this->isDocumented;
	}


	/**
	 * @return bool
	 */
	public function isDeprecated()
	{
		if ($this->reflection->isDeprecated()) {
			return TRUE;
		}

		if (($this instanceof ReflectionMethod || $this instanceof ReflectionProperty || $this instanceof ReflectionConstant)
			&& $class = $this->getDeclaringClass()
		) {
			return $class->isDeprecated();
		}

		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function inPackage()
	{
		return ($this->getPackageName() !== '');
	}


	/**
	 * @return string
	 */
	public function getPackageName()
	{
		static $packages = [];

		if ($package = $this->getAnnotation('package')) {
			$packageName = preg_replace('~\s+.*~s', '', $package[0]);
			if (empty($packageName)) {
				return '';
			}

			if ($subpackage = $this->getAnnotation('subpackage')) {
				$subpackageName = preg_replace('~\s+.*~s', '', $subpackage[0]);
				if ( ! empty($subpackageName) && strpos($subpackageName, $packageName) === 0) {
					$packageName = $subpackageName;

				} else {
					$packageName .= '\\' . $subpackageName;
				}
			}
			$packageName = strtr($packageName, '._/', '\\\\\\');

			$lowerPackageName = strtolower($packageName);
			if ( ! isset($packages[$lowerPackageName])) {
				$packages[$lowerPackageName] = $packageName;
			}

			return $packages[$lowerPackageName];
		}

		return '';
	}


	/**
	 * Returns element package name (including subpackage name).
	 * For internal elements returns "PHP", for elements in global space returns "None".
	 *
	 * @return string
	 */
	public function getPseudoPackageName()
	{
		if ($this->isInternal()) {
			return 'PHP';
		}

		return $this->getPackageName() ?: 'None';
	}


	/**
	 * @return bool
	 */
	public function inNamespace()
	{
		return $this->getNamespaceName() !== '';
	}


	/**
	 * @return string
	 */
	public function getNamespaceName()
	{
		static $namespaces = [];

		$namespaceName = $this->reflection->getNamespaceName();

		if ( ! $namespaceName) {
			return $namespaceName;
		}

		$lowerNamespaceName = strtolower($namespaceName);
		if ( ! isset($namespaces[$lowerNamespaceName])) {
			$namespaces[$lowerNamespaceName] = $namespaceName;
		}

		return $namespaces[$lowerNamespaceName];
	}


	/**
	 * Returns element namespace name.
	 * For internal elements returns "PHP", for elements in global space returns "None".
	 *
	 * @return string
	 */
	public function getPseudoNamespaceName()
	{
		return $this->isInternal() ? 'PHP' : $this->getNamespaceName() ?: 'None';
	}


	/**
	 * @return string[]
	 */
	public function getNamespaceAliases()
	{
		return $this->reflection->getNamespaceAliases();
	}


	/**
	 * @return string
	 */
	public function getShortDescription()
	{
		$short = $this->reflection->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION);
		if ( ! empty($short)) {
			return $short;
		}

		if ($this instanceof ReflectionProperty || $this instanceof ReflectionConstant) {
			$var = $this->getAnnotation('var');
			list(, $short) = preg_split('~\s+|$~', $var[0], 2);
		}

		return $short;
	}


	/**
	 * @return string
	 */
	public function getLongDescription()
	{
		$short = $this->getShortDescription();
		$long = $this->reflection->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION);

		if ( ! empty($long)) {
			$short .= "\n\n" . $long;
		}

		return $short;
	}


	/**
	 * @return string|bool
	 */
	public function getDocComment()
	{
		return $this->reflection->getDocComment();
	}


	/**
	 * Returns reflection element annotations.
	 * Removes the short and long description.
	 * In case of classes, functions and constants, @package, @subpackage, @author and @license annotations
	 * are added from declaring files if not already present.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		if ($this->annotations === NULL) {
			$annotations = $this->reflection->getAnnotations();
			$annotations = array_change_key_case($annotations, CASE_LOWER);

			unset($annotations[ReflectionAnnotation::SHORT_DESCRIPTION]);
			unset($annotations[ReflectionAnnotation::LONG_DESCRIPTION]);

			$annotations += $this->getAnnotationsFromReflection($this->reflection);
			$this->annotations = $annotations;
		}

		return $this->annotations;
	}


	/**
	 * @param string $annotation
	 * @return array
	 */
	public function getAnnotation($annotation)
	{
		$annotations = $this->getAnnotations();
		return isset($annotations[$annotation]) ? $annotations[$annotation] : NULL;
	}


	/**
	 * @param string $annotation
	 * @return bool
	 */
	public function hasAnnotation($annotation)
	{
		$annotations = $this->getAnnotations();
		return isset($annotations[$annotation]);
	}


	/**
	 * @param string $annotation
	 * @param string $value
	 * @return ReflectionElement
	 */
	public function addAnnotation($annotation, $value)
	{
		if ($this->annotations === NULL) {
			$this->getAnnotations();
		}
		$this->annotations[$annotation][] = $value;

		return $this;
	}


	/**
	 * @return TokenReflection\Invalid\ReflectionElement
	 */
	public function addReason(BaseException $reason)
	{
		$this->reasons[] = $reason;
		return $this;
	}


	/**
	 * @return TokenReflection\Invalid\ReflectionElement
	 */
	public function getReasons()
	{
		return $this->reasons;
	}


	/**
	 * @return bool
	 */
	public function hasReasons()
	{
		return ! empty($this->reasons);
	}


	/**
	 * @param mixed $reflection
	 * @return array
	 */
	private function getAnnotationsFromReflection($reflection)
	{
		$fileLevel = [
			'package' => TRUE,
			'subpackage' => TRUE,
			'author' => TRUE,
			'license' => TRUE,
			'copyright' => TRUE
		];

		$annotations = [];
		if ($reflection instanceof ReflectionClass || $reflection instanceof ReflectionFunction
			|| ($reflection instanceof ReflectionConstant  && $reflection->getDeclaringClassName() === NULL)
		) {
			foreach ($reflection->getFileReflection()->getAnnotations() as $name => $value) {
				if (isset($fileLevel[$name]) && empty($annotations[$name])) {
					$annotations[$name] = $value;
				}
			}
		}
		return $annotations;
	}

}
