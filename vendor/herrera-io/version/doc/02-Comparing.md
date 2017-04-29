Comparing
=========

The `Herrera\Version\Comparator` class will compare any two instances of
`Herrera\Version\Version`, which also includes `Herrera\Version\Builder`.

Simple Comparison
-----------------

To perform a simple comparison, you may call the `compareTo()` method:

```php
use Herrera\Version\Comparator;
use Herrera\Version\Version;

$left = new Version(1, 0, 0);
$right = new Version(1, 0, 0);

$result = Comparator::compareTo($left, $right); // returns "0" (zero)
```

You may use any of the following class constants to check the result:

- `Comparator::EQUAL_TO` (0, zero)
- `Comparator::GREATER_THAN` (1, one)
- `Comparator::LESS_THAN` (-1, negative one)

Equality
--------

To check if two versions are equal, you may call the `isEqualTo()` method:

```php
$result = Comparator::isEqualTo($left, $right); // returns "true"
```

Greater Than
---------

To check if one version is greater than another, you may call the
`isGreaterThan()` method. The method will check of the first version (`$left`)
is greater than the second (`$right`):

```php
$left = new Version(2, 0, 0);
$right = new Version(1, 0, 0);

$result = Comparator::isGreaterThan($left, $right); // returns "true"
```

Less Than
---------

To check if one version is less than another, you may call the `isGreaterThan()`
method. The method will check of the first version (`$left`) is less than the
second (`$right`):

```php
$left = new Version(1, 0, 0);
$right = new Version(2, 0, 0);

$result = Comparator::isLessThan($left, $right); // returns "true"
```
