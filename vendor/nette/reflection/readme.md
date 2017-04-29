Nette PHP Reflection
====================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/reflection.svg)](https://packagist.org/packages/nette/reflection)
[![Build Status](https://travis-ci.org/nette/reflection.svg?branch=master)](https://travis-ci.org/nette/reflection)
[![Coverage Status](https://coveralls.io/repos/github/nette/reflection/badge.svg?branch=master)](https://coveralls.io/github/nette/reflection?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/reflection/v/stable)](https://github.com/nette/reflection/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/reflection/blob/master/license.md)

If you need to find every information about any class, reflection is the right tool to do it. You can easily find out which methods does any class have, what parameters do those methods accept, etc. `Nette\Object` simplifies access to class' self-reflection with method `getReflection()`, returning a [ClassType | api:Nette\Reflection\ClassType]) object:

```php
// getting PDO class reflection
$classReflection = new Nette\Reflection\ClassType('PDO');

// getting PDO::query method reflection
$methodReflection = new Nette\Reflection\Method('PDO', 'query');
```

Annotations
-----------

Reflection has really a lot to do with annotations. The annotations are written into phpDoc comments (two opening asterisks are mandatory!) and start with `@`. You can annotate classes, variables and methods:

```php
/**
 * @author John Doe
 * @author Tomas Marny
 * @secured
 */
class FooClass
{
	/** @Persistent */
	public $foo;

	/** @User(loggedIn, role=Admin) */
	public function bar() {}
}
```

In the code there are these annotations:

- `@author John Doe` - string, contains text value `'John Doe'`
- `@Persistent` - boolean, its presence means `TRUE`
- `@User(loggedIn, role=Admin)` - contains associative `array('loggedIn', 'role' => 'Admin')`


The existence of a class annotation can be checked via `hasAnnotation()` method:


```php
$fooReflection = new Nette\Reflection\ClassType('FooClass');
$fooReflection->hasAnnotation('author'); // returns TRUE
$fooReflection->hasAnnotation('copyright'); // returns FALSE
```

Values can be acquired with `getAnnotation()`:

```php
$fooReflection->getAnnotation('author'); // returns string 'Tomas Marny'

$fooReflection->getMethod('bar')->getAnnotation('User');
// returns array('loggedIn', 'role' => 'Admin')
```

.[caution]
Previous definitions are overwritten with the latter ones, sou you will always get the last one.

All annotations can be obtained with `getAnnotations()`:

```
array(3) {
	"author" => array(2) {
		0 => string(8) "John Doe"
		1 => string(11) "Tomas Marny"
	}
	"secured" => array(1) {
		0 => bool(TRUE)
	}
}
```
