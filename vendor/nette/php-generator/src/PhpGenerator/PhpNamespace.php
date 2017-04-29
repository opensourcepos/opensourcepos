<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use Nette\InvalidStateException;
use Nette\Utils\Strings;


/**
 * Namespaced part of a PHP file.
 *
 * Generates:
 * - namespace statement
 * - variable amount of use statements
 * - one or more class declarations
 */
final class PhpNamespace
{
	use Nette\SmartObject;

	private static $keywords = [
		'string' => 1, 'int' => 1, 'float' => 1, 'bool' => 1, 'array' => 1,
		'callable' => 1, 'iterable' => 1, 'void' => 1, 'self' => 1, 'parent' => 1,
	];

	/** @var string */
	private $name;

	/** @var bool */
	private $bracketedSyntax = FALSE;

	/** @var string[] */
	private $uses = [];

	/** @var ClassType[] */
	private $classes = [];


	public function __construct(string $name)
	{
		if ($name !== '' && !Helpers::isNamespaceIdentifier($name)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid name.");
		}
		$this->name = $name;
	}


	/** @deprecated */
	public function setName($name)
	{
		trigger_error(__METHOD__ . '() is deprecated, use constructor.', E_USER_DEPRECATED);
		$this->__construct($name);
		return $this;
	}


	public function getName(): string
	{
		return $this->name;
	}


	/**
	 * @return static
	 * @internal
	 */
	public function setBracketedSyntax(bool $state = TRUE): self
	{
		$this->bracketedSyntax = $state;
		return $this;
	}


	public function getBracketedSyntax(): bool
	{
		return $this->bracketedSyntax;
	}


	/**
	 * @throws InvalidStateException
	 * @return static
	 */
	public function addUse(string $name, string $alias = NULL, string &$aliasOut = NULL): self
	{
		$name = ltrim($name, '\\');
		if ($alias === NULL && $this->name === Helpers::extractNamespace($name)) {
			$alias = Helpers::extractShortName($name);
		}
		if ($alias === NULL) {
			$path = explode('\\', $name);
			$counter = NULL;
			do {
				if (empty($path)) {
					$counter++;
				} else {
					$alias = array_pop($path) . $alias;
				}
			} while (isset($this->uses[$alias . $counter]) && $this->uses[$alias . $counter] !== $name);
			$alias .= $counter;

		} elseif (isset($this->uses[$alias]) && $this->uses[$alias] !== $name) {
			throw new InvalidStateException(
				"Alias '$alias' used already for '{$this->uses[$alias]}', cannot use for '{$name}'."
			);
		}

		$aliasOut = $alias;
		$this->uses[$alias] = $name;
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getUses(): array
	{
		return $this->uses;
	}


	public function unresolveName(string $name): string
	{
		if (isset(self::$keywords[strtolower($name)]) || $name === '') {
			return $name;
		}
		$name = ltrim($name, '\\');
		$res = NULL;
		$lower = strtolower($name);
		foreach ($this->uses as $alias => $for) {
			if (Strings::startsWith($lower . '\\', strtolower($for) . '\\')) {
				$short = $alias . substr($name, strlen($for));
				if (!isset($res) || strlen($res) > strlen($short)) {
					$res = $short;
				}
			}
		}

		if (!$res && Strings::startsWith($lower, strtolower($this->name) . '\\')) {
			return substr($name, strlen($this->name) + 1);
		} else {
			return $res ?: ($this->name ? '\\' : '') . $name;
		}
	}


	public function addClass(string $name): ClassType
	{
		if (isset($this->classes[$name])) {
			trigger_error(__METHOD__ . "() class $name was already added.", E_USER_DEPRECATED);
			return $this->classes[$name];
		}
		$this->addUse($this->name . '\\' . $name);
		return $this->classes[$name] = new ClassType($name, $this);
	}


	public function addInterface(string $name): ClassType
	{
		return $this->addClass($name)->setType(ClassType::TYPE_INTERFACE);
	}


	public function addTrait(string $name): ClassType
	{
		return $this->addClass($name)->setType(ClassType::TYPE_TRAIT);
	}


	/**
	 * @return ClassType[]
	 */
	public function getClasses(): array
	{
		return $this->classes;
	}


	public function __toString(): string
	{
		$uses = [];
		asort($this->uses);
		foreach ($this->uses as $alias => $name) {
			$useNamespace = Helpers::extractNamespace($name);

			if ($this->name !== $useNamespace) {
				if ($alias === $name || substr($name, -(strlen($alias) + 1)) === '\\' . $alias) {
					$uses[] = "use {$name};";
				} else {
					$uses[] = "use {$name} as {$alias};";
				}
			}
		}

		$body = ($uses ? implode("\n", $uses) . "\n\n" : '')
			. implode("\n", $this->classes);

		if ($this->bracketedSyntax) {
			return 'namespace' . ($this->name ? ' ' . $this->name : '') . " {\n\n"
				. Strings::indent($body)
				. "\n}\n";

		} else {
			return ($this->name ? "namespace {$this->name};\n\n" : '')
				. $body;
		}
	}

}
