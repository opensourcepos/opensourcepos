<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator;

use Nette;
use Nette\Utils\Strings;


/**
 * Class/Interface/Trait description.
 *
 * @property Method[] $methods
 * @property Property[] $properties
 */
final class ClassType
{
	use Nette\SmartObject;
	use Traits\CommentAware;

	const TYPE_CLASS = 'class';
	const TYPE_INTERFACE = 'interface';
	const TYPE_TRAIT = 'trait';

	/** @var PhpNamespace|NULL */
	private $namespace;

	/** @var string|NULL */
	private $name;

	/** @var string  class|interface|trait */
	private $type = 'class';

	/** @var bool */
	private $final = FALSE;

	/** @var bool */
	private $abstract = FALSE;

	/** @var string|string[] */
	private $extends = [];

	/** @var string[] */
	private $implements = [];

	/** @var string[] */
	private $traits = [];

	/** @var Constant[] name => Constant */
	private $consts = [];

	/** @var Property[] name => Property */
	private $properties = [];

	/** @var Method[] name => Method */
	private $methods = [];


	/**
	 * @param  string|object
	 * @return static
	 */
	public static function from($class): self
	{
		if ($class instanceof \ReflectionClass) {
			trigger_error(__METHOD__ . '() accepts only class name or object.', E_USER_DEPRECATED);
		}
		return (new Factory)->fromClassReflection(
			$class instanceof \ReflectionClass ? $class : new \ReflectionClass($class)
		);
	}


	public function __construct(string $name = NULL, PhpNamespace $namespace = NULL)
	{
		$this->setName($name);
		$this->namespace = $namespace;
	}


	public function __toString(): string
	{
		$traits = [];
		foreach ($this->traits as $trait => $resolutions) {
			$traits[] = 'use ' . ($this->namespace ? $this->namespace->unresolveName($trait) : $trait)
				. ($resolutions ? " {\n\t" . implode(";\n\t", $resolutions) . ";\n}" : ';');
		}

		$consts = [];
		foreach ($this->consts as $const) {
			$consts[] = Helpers::formatDocComment((string) $const->getComment())
				. ($const->getVisibility() ? $const->getVisibility() . ' ' : '')
				. 'const ' . $const->getName() . ' = ' . Helpers::dump($const->getValue()) . ';';
		}

		$properties = [];
		foreach ($this->properties as $property) {
			$properties[] = Helpers::formatDocComment((string) $property->getComment())
				. ($property->getVisibility() ?: 'public') . ($property->isStatic() ? ' static' : '') . ' $' . $property->getName()
				. ($property->getValue() === NULL ? '' : ' = ' . Helpers::dump($property->getValue()))
				. ';';
		}

		$mapper = function (array $arr) {
			return $this->namespace ? array_map([$this->namespace, 'unresolveName'], $arr) : $arr;
		};

		return Strings::normalize(
			Helpers::formatDocComment($this->comment . "\n")
			. ($this->abstract ? 'abstract ' : '')
			. ($this->final ? 'final ' : '')
			. ($this->name ? "$this->type $this->name " : '')
			. ($this->extends ? 'extends ' . implode(', ', $mapper((array) $this->extends)) . ' ' : '')
			. ($this->implements ? 'implements ' . implode(', ', $mapper($this->implements)) . ' ' : '')
			. ($this->name ? "\n" : '') . "{\n"
			. Strings::indent(
				($this->traits ? implode("\n", $traits) . "\n\n" : '')
				. ($this->consts ? implode("\n", $consts) . "\n\n" : '')
				. ($this->properties ? implode("\n\n", $properties) . "\n\n" : '')
				. ($this->methods ? "\n" . implode("\n\n\n", $this->methods) . "\n\n" : ''), 1)
			. '}'
		) . ($this->name ? "\n" : '');
	}


	/**
	 * @return PhpNamespace|NULL
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}


	/**
	 * @param  string|NULL
	 * @return static
	 */
	public function setName($name): self
	{
		if ($name !== NULL && !Helpers::isIdentifier($name)) {
			throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
		}
		$this->name = $name;
		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return static
	 */
	public function setType(string $type): self
	{
		if (!in_array($type, ['class', 'interface', 'trait'], TRUE)) {
			throw new Nette\InvalidArgumentException('Argument must be class|interface|trait.');
		}
		$this->type = $type;
		return $this;
	}


	public function getType(): string
	{
		return $this->type;
	}


	/**
	 * @return static
	 */
	public function setFinal(bool $state = TRUE): self
	{
		$this->final = $state;
		return $this;
	}


	public function isFinal(): bool
	{
		return $this->final;
	}


	/**
	 * @return static
	 */
	public function setAbstract(bool $state = TRUE): self
	{
		$this->abstract = $state;
		return $this;
	}


	public function isAbstract(): bool
	{
		return $this->abstract;
	}


	/**
	 * @param  string|string[]
	 * @return static
	 */
	public function setExtends($names): self
	{
		if (!is_string($names) && !is_array($names)) {
			throw new Nette\InvalidArgumentException('Argument must be string or string[].');
		}
		$this->validate((array) $names);
		$this->extends = $names;
		return $this;
	}


	/**
	 * @return string|string[]
	 */
	public function getExtends()
	{
		return $this->extends;
	}


	/**
	 * @return static
	 */
	public function addExtend(string $name): self
	{
		$this->validate([$name]);
		$this->extends = (array) $this->extends;
		$this->extends[] = $name;
		return $this;
	}


	/**
	 * @param  string[]
	 * @return static
	 */
	public function setImplements(array $names): self
	{
		$this->validate($names);
		$this->implements = $names;
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getImplements(): array
	{
		return $this->implements;
	}


	/**
	 * @return static
	 */
	public function addImplement(string $name): self
	{
		$this->validate([$name]);
		$this->implements[] = $name;
		return $this;
	}


	/**
	 * @param  string[]
	 * @return static
	 */
	public function setTraits(array $names): self
	{
		$this->validate($names);
		$this->traits = array_fill_keys($names, []);
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getTraits(): array
	{
		return array_keys($this->traits);
	}


	/**
	 * @return static
	 */
	public function addTrait(string $name, array $resolutions = []): self
	{
		$this->validate([$name]);
		$this->traits[$name] = $resolutions;
		return $this;
	}


	/**
	 * @deprecated  use setConstants()
	 * @return static
	 */
	public function setConsts(array $consts): self
	{
		trigger_error(__METHOD__ . '() is deprecated, use setConstants()', E_USER_DEPRECATED);
		return $this->setConstants($consts);
	}


	/**
	 * @deprecated  use getConstants()
	 */
	public function getConsts(): array
	{
		trigger_error(__METHOD__ . '() is deprecated, use similar getConstants()', E_USER_DEPRECATED);
		return array_map(function ($const) { return $const->getValue(); }, $this->consts);
	}


	/**
	 * @deprecated  use addConstant()
	 * @return static
	 */
	public function addConst(string $name, $value): self
	{
		trigger_error(__METHOD__ . '() is deprecated, use similar addConstant()', E_USER_DEPRECATED);
		$this->addConstant($name, $value);
		return $this;
	}


	/**
	 * @param  Constant[]|mixed[]
	 * @return static
	 */
	public function setConstants(array $consts): self
	{
		$this->consts = [];
		foreach ($consts as $k => $v) {
			$const = $v instanceof Constant ? $v : (new Constant($k))->setValue($v);
			$this->consts[$const->getName()] = $const;
		}
		return $this;
	}


	/**
	 * @return Constant[]
	 */
	public function getConstants(): array
	{
		return $this->consts;
	}


	public function addConstant(string $name, $value): Constant
	{
		return $this->consts[$name] = (new Constant($name))->setValue($value);
	}


	/**
	 * @param  Property[]
	 * @return static
	 */
	public function setProperties(array $props): self
	{
		$this->properties = [];
		foreach ($props as $v) {
			if (!$v instanceof Property) {
				throw new Nette\InvalidArgumentException('Argument must be Nette\PhpGenerator\Property[].');
			}
			$this->properties[$v->getName()] = $v;
		}
		return $this;
	}


	/**
	 * @return Property[]
	 */
	public function getProperties(): array
	{
		return $this->properties;
	}


	public function getProperty($name): Property
	{
		if (!isset($this->properties[$name])) {
			throw new Nette\InvalidArgumentException("Property '$name' not found.");
		}
		return $this->properties[$name];
	}


	/**
	 * @param  string  without $
	 */
	public function addProperty(string $name, $value = NULL): Property
	{
		return $this->properties[$name] = (new Property($name))->setValue($value);
	}


	/**
	 * @param  Method[]
	 * @return static
	 */
	public function setMethods(array $methods): self
	{
		$this->methods = [];
		foreach ($methods as $v) {
			if (!$v instanceof Method) {
				throw new Nette\InvalidArgumentException('Argument must be Nette\PhpGenerator\Method[].');
			}
			$this->methods[$v->getName()] = $v->setNamespace($this->namespace);
		}
		return $this;
	}


	/**
	 * @return Method[]
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}


	public function getMethod($name): Method
	{
		if (!isset($this->methods[$name])) {
			throw new Nette\InvalidArgumentException("Method '$name' not found.");
		}
		return $this->methods[$name];
	}


	public function addMethod(string $name): Method
	{
		$method = (new Method($name))->setNamespace($this->namespace);
		if ($this->type === 'interface') {
			$method->setBody(NULL);
		} else {
			$method->setVisibility('public');
		}
		return $this->methods[$name] = $method;
	}


	private function validate(array $names)
	{
		foreach ($names as $name) {
			if (!Helpers::isNamespaceIdentifier($name, TRUE)) {
				throw new Nette\InvalidArgumentException("Value '$name' is not valid class name.");
			}
		}
	}

}
