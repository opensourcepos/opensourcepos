Nette PHP Generator
===================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/php-generator.svg)](https://packagist.org/packages/nette/php-generator)
[![Build Status](https://travis-ci.org/nette/php-generator.svg?branch=master)](https://travis-ci.org/nette/php-generator)
[![Coverage Status](https://coveralls.io/repos/github/nette/php-generator/badge.svg?branch=master&v=1)](https://coveralls.io/github/nette/php-generator?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/php-generator/v/stable)](https://github.com/nette/php-generator/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/php-generator/blob/master/license.md)

Generate PHP code, classes, namespaces etc. with a simple programmatical API.

Usage is very easy. In first, install it using Composer:

```
composer require nette/php-generator
```

Examples
--------

```php
$class = new Nette\PhpGenerator\ClassType('Demo');

$class
	->setAbstract()
	->setFinal()
	->setExtends('ParentClass')
	->addImplement('Countable')
	->addTrait('Nette\SmartObject')
	->addComment("Description of class.\nSecond line\n")
	->addComment('@property-read Nette\Forms\Form $form');

$class->addConstant('ID', 123);

$class->addProperty('items', [1, 2, 3])
	->setVisibility('private')
	->setStatic()
	->addComment('@var int[]');

$method = $class->addMethod('count')
	->addComment('Count it.')
	->addComment('@return int')
	->setFinal()
	->setVisibility('protected')
	->setBody('return count($items ?: $this->items);');

$method->addParameter('items', []) // $items = []
		->setReference() // &$items = []
		->setTypeHint('array'); // array &$items = []
```

To generate PHP code simply cast to string or use echo:

```php
echo $class;
```

It will render this result:

```php
/**
 * Description of class.
 * Second line
 *
 * @property-read Nette\Forms\Form $form
 */
abstract final class Demo extends ParentClass implements Countable
{
	use Nette\SmartObject;

	const ID = 123;

	/** @var int[] */
	private static $items = [1, 2, 3];

	/**
	 * Count it.
	 * @return int
	 */
	final protected function count(array &$items = [])
	{
		return count($items ?: $this->items);
	}

}
```

PHP Generator supports all new PHP 7.1 features:

```php
$class = new Nette\PhpGenerator\ClassType('Demo');

$class->addConstant('ID', 123)
	->setVisibility('private'); // constant visiblity

$method = $class->addMethod('getValue')
	->setReturnType('int') // method return type
	->setReturnNullable() // nullable return type
	->setBody('return count($this->items);');

$method->addParameter('id')
		->setTypeHint('int') // scalar type hint
		->setNullable(); // nullable type hint

echo $class;
```

Result:

```php
class Demo
{
	private const ID = 123;

	public function getValue(?int $id): ?int
	{
		return count($this->items);
	}

}
```

Literals
--------

You can pass any PHP code to property or parameter default values via `Nette\PhpGenerator\PhpLiteral`:

```php
use Nette\PhpGenerator\PhpLiteral;

$class = new Nette\PhpGenerator\ClassType('Demo');

$class->addProperty('foo', new PhpLiteral('Iterator::SELF_FIRST'));

$class->addMethod('bar')
	->addParameter('id', new PhpLiteral('1 + 2'));

echo $class;
```

Result:

```php
class Demo
{
	public $foo = Iterator::SELF_FIRST;

	public function bar($id = 1 + 2)
	{
	}

}
```

Interface or trait
------------------

```php
$class = new Nette\PhpGenerator\ClassType('DemoInterface');
$class->setType('interface');
$class->setType('trait'); // or trait
```

Trait resolutions and visibility
--------------------------------

```php
$class = new Nette\PhpGenerator\ClassType('Demo');
$class->addTrait('SmartObject', ['sayHello as protected']);
echo $class;
```

Result:

```php
class Demo
{
	use SmartObject {
		sayHello as protected;
	}
}
```

Anonymous class
---------------

```php
$class = new Nette\PhpGenerator\ClassType(NULL);
$class->addMethod('__construct')
	->addParameter('foo');

echo '$obj = new class ($val) ' . $class . ';';
```

Result:

```php
$obj = new class ($val) {

	public function __construct($foo)
	{
	}
};
```

Global function
---------------

```php
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->setBody('return $a + $b;');
$function->addParameter('a');
$function->addParameter('b');
echo $function;
```

Result:

```php
function foo($a, $b)
{
	return $a + $b;
}
```

Closure
-------

```php
$closure = new Nette\PhpGenerator\Closure;
$closure->setBody('return $a + $b;');
$closure->addParameter('a');
$closure->addParameter('b');
$closure->addUse('c')
	->setReference();
echo $closure;
```

Result:

```php
function ($a, $b) use (&$c) {
	return $a + $b;
}
```

Method body generator
---------------------

You can use special placeholders for handy way to generate method or function body.

Simple placeholders:

```php
$str = 'any string';
$num = 3;
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->addBody('$a = strlen(?, ?);', [$str, $num]);
$function->addBody('return $a \? 10 : ?;', [$num]); // escaping
echo $function;
```

Result:

```php
function foo()
{
	$a = strlen('any string', 3);
	return $a ? 10 : 3;
}
```

Variadic placeholder:

```php
$items = [1, 2, 3];
$function = new Nette\PhpGenerator\GlobalFunction('foo');
$function->setBody('myfunc(...?);', [$items]);
echo $function;
```

Result:

```php
function foo()
{
	myfunc(1, 2, 3);
}
```


Namespace
---------

```php
$namespace = new Nette\PhpGenerator\PhpNamespace('Foo');
$namespace->addUse('Bar\AliasedClass');

$class = $namespace->addClass('Demo');
$class->addImplement('Foo\A') // resolves to A
	->addTrait('Bar\AliasedClass'); // resolves to AliasedClass

$method = $class->addMethod('method');
$method->addParameter('arg')
	->setTypeHint('Bar\OtherClass'); // resolves to \Bar\OtherClass

echo $namespace;
```

Result:

```php
namespace Foo;

use Bar\AliasedClass;

class Demo implements A
{
	use AliasedClass;

	public function method(\Bar\OtherClass $arg)
	{
	}

}
```

Factories
---------

Another common use case is to create class or method form existing ones:

```php
$class = Nette\PhpGenerator\ClassType::from(PDO::class);

$function = Nette\PhpGenerator\GlobalFunction::from('trim');

$closure = Nette\PhpGenerator\Closure::from(
	function (stdClass $a, $b = NULL) {}
);
```
