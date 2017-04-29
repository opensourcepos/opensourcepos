Nette Utility Classes
=====================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/utils.svg)](https://packagist.org/packages/nette/utils)
[![Build Status](https://travis-ci.org/nette/utils.svg?branch=master)](https://travis-ci.org/nette/utils)
[![Coverage Status](https://coveralls.io/repos/github/nette/utils/badge.svg?branch=master)](https://coveralls.io/github/nette/utils?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/utils/v/stable)](https://github.com/nette/utils/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/utils/blob/master/license.md)

Nette\SmartObject: Strict classes
---------------------------------

PHP gives a huge freedom to developers, which makes it a perfect language for making mistakes. But you can stop this bad behavior and start writing applications without hardly discoverable mistakes. Do you wonder how? It's really simple -- you just need to have stricter rules.

Can you find an error in this example?

```php
class Circle
{
	public $radius;

	public function getArea()
	{
		return $this->radius * $this->radius * M_PI;
	}

}

$circle = new Circle;
$circle->raduis = 10;
echo $circle->getArea(); // 10² * π ≈ 314
```

On the first look it seems that code will print out 314; but it returns 0. How is this even possible? Accidentaly, `$circle->radius` was mistyped to `raduis`. Just a small typo, which will give you a hard time correcting it, because PHP does not say a thing when something is wrong. Not even a Warning or Notice error message. Because PHP does not think it is an error.

The mentioned mistake could be corrected immediately, if class `Circle` would use trait Nette\SmartObject:

```php
class Circle
{
	use Nette\SmartObject;
	...
```

Whereas the former code executed successfully (although it contained an error), the latter did not:

![](https://files.nette.org/git/doc-2.1/debugger-circle.png)

Trait `Nette\SmartObject` made `Circle` more strict and threw an exception when you tried to access an undeclared property. And `Tracy\Debugger` displayed error message about it. Line of code with fatal typo is now highlighted and error message has meaningful description: *Cannot write to an undeclared property Circle::$raduis*. Programmer can now fix the mistake he might have otherwise missed and which could be a real pain to find later.

One of many remarkable abilities of `Nette\SmartObject` is throwing exceptions when accessing undeclared members.

```php
$circle = new Circle;
echo $circle->undeclared; // throws Nette\MemberAccessException
$circle->undeclared = 1; // throws Nette\MemberAccessException
$circle->unknownMethod(); // throws Nette\MemberAccessException
```

But it has much more to offer!


Properties, getters a setters
-----------------------------

In modern object oriented languages *property* describes members of class, which look like variables but are represented by methods. When reading or assigning values to those "variables", methods are called instead (so-called getters and setters). It is really useful feature, which allows us to control the access to these variables. Using this we can validate inputs or postpone the computation of values of these variables to the time when it is actually accessed.

Any class that uses `Nette\SmartObject` acquires the ability to imitate properties. Only thing you need to do is to keep simple convention:

- Add annotation `@property type $xyz`
- Getter's name is `getXyz()` or `isXyz()`, setter's is `setXyz()`
- It is possible to have `@property-read` only and `@property-write` only properties
- Names of properties are case-sensitive (first letter being an exception)

We will make use of properties in the class Circle to make sure variable `$radius` contains only non-negative numbers:

```php
/**
 * @property float $radius
 * @property-read float $area
 * @property-read bool $visible
 */
class Circle
{
	use Nette\SmartObject;

	private $radius; // not public anymore!

	protected function getRadius()
	{
		return $this->radius;
	}

	protected function setRadius($radius)
	{
		// sanitizing value before saving it
		$this->radius = max(0.0, (float) $radius);
	}

	protected function getArea()
	{
		return $this->radius * $this->radius * M_PI;
	}

	protected function isVisible()
	{
		return $this->radius > 0;
	}

}

$circle = new Circle;
$circle->radius = 10; // calls setRadius()
echo $circle->area; // calls getArea()
echo $circle->visible; // calls $circle->isVisible()
```

Properties are mostly a syntactic sugar to beautify the code and make programmer's life easier. You do not have to use them, if you don't want to.

Events
------

Now we are going to create functions, which will be called when border radius changes. Let's call it `change` event and those functions event handlers:

```php
class Circle
{
	use Nette\SmartObject;

	/** @var array */
	public $onChange;

	public function setRadius($radius)
	{
		// call all handlers in array $onChange
		$this->onChange($this, $this->radius, $radius);

		$this->radius = max(0.0, (float) $radius);
	}
}

$circle = new Circle;

// adding an event handler
$circle->onChange[] = function ($circle, $oldValue, $newValue) {
	echo 'there was a change!';
};

$circle->setRadius(10);
```

There is another syntactic sugar in `setRadius`'s code. Instead of iteration on `$onChange` array and calling each method one by one with unreliable (does not report if callback has any errors) function call_user_func, you just have to write simple `onChange(...)` and given parameters will be handed over to the handlers.
