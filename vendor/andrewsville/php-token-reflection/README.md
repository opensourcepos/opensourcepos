# PHP Token Reflection #

[![Build Status](https://secure.travis-ci.org/Andrewsville/PHP-Token-Reflection.png?branch=develop)](http://travis-ci.org/Andrewsville/PHP-Token-Reflection)

In short, this library emulates the PHP reflection model using the tokenized PHP source.

The basic concept is, that any reflection is possible to process the particular part of the token array describing the reflected element. It is also able to find out if there are any child elements (a class reflection is able to find method definitions in the source, for example), create their reflections and pass the appropriate part of the token array to them.

This concept allows us to keep the parser code relatively simple and easily maintainable. And we are able to to create all reflections in a single pass. That is absolutely crucial for the performance of the library.

All reflection instances are being kept in a [TokenReflection\\Broker](https://github.com/Andrewsville/PHP-Token-Reflection/blob/master/library/TokenReflection/Broker.php) instance and all reflections know the broker that created them. This is very important, because a class reflection, for example, holds all its constants, methods and properties reflections instantiated inside, however it knows absolutely nothing about its parent class or the interfaces it implements. It knows just their fully qualified names. So when you call ```$reflectionClass->getParentClass();```, the class reflection asks the Broker for a reflection of a class by its name and returns it.

An interesting thing happens when there is a parent class defined but it was not processed (in other words, you ask the Broker for a class that it does not know). It still returns a reflection! Yes, we do have reflections for classes that do not exist! COOL!

There are reflections for file (\*), file-namespace (\*), namespace, class, function/method, constant, property and parameter. You will not normally get in touch with those marked with an asterisk but they are used internally.

**ReflectionFile** is the topmost structure in our reflection tree. It gets the whole tokenized source and tries to find namespaces there. If it does, it creates ReflectionFileNamespace instances and passes them the appropriate part of the tokens array. If not, it creates a single pseudo-namespace (called no-namespace) a passes the whole tokenized source to it.

**ReflectionFileNamespace** gets the namespace definition from the file, finds out its name, other aliased namespaces and tries to find any defined constants, functions and classes. If it finds any, it creates their reflections and passes them the appropriate parts of the tokens array.

**ReflectionNamespace** is a similar (in name) yet quite different (in meaning) structure. It is a unique structure for every namespace and it holds all constants, functions and classes from this particular namespace inside. In fact, it is a simple container. It also is not created directly by any parent reflection, but the Broker creates it.

Why do we need two separate classes? Because namespaces can be split into many files and in each file it can have individual namespace aliases. And those have to be taken into consideration when resolving parent class/interface names. It means that a ReflectionFileNamespace is created for every namespace in every file and it parses its contents, resolves fully qualified names of all classes, their parents and interfaces. Later, the Broker takes all ReflectionFileNamespace instances of the same namespace and merges them into a single ReflectionNameaspace instance.

**ReflectionClass**, **ReflectionFunction**, **ReflectionMethod**, **ReflectionParameter** and **ReflectionProperty** work the same way like their internal reflection namesakes.

**ReflectionConstants** is our addition to the reflection model. There is not much it can do - it can return its name, value (we will speak about values later) and how it was defined.

(Almost) all reflection classes share a common base class, that defines some common functionality and interface. This means that our reflection model is much more unified than the internal one.

There are reflections for the tokenized source (those mentioned above), but also descendants of the internal reflection that implement our additional features (they both use the same interface). They represent the PHP's internal classes, functions, ... So when you ask the Broker for an internal class, it returns a [TokenReflection\\Php\\ReflectionClass](https://github.com/Andrewsville/PHP-Token-Reflection/blob/master/library/TokenReflection/Php/ReflectionClass.php) instance that encapsulates the internal reflection functionality and adds our features. And there is also the [TokenReflection\\Php\\ReflectionConstant](https://github.com/Andrewsville/PHP-Token-Reflection/blob/master/library/TokenReflection/Php/ReflectionConstant.php) class that has no parent in the internal reflection model.

## Remarks

From the beginning we tried to be as compatible as possible with the internal reflection (including things like returning the interface list in the same - pretty weird - order). However there are situations where it is just impossible (for example we prefer consistency over compatibility with the internal reflection and will not introduce [this bug](https://bugs.php.net/bug.php?id=62715) into the library :).

We are limited in the way we can handle constant values and property and parameter default values. When defined as a constant, we do our best to resolve its value (within parsed and internal constants) and use it. This is eventually made via a combination of ```var_export()``` and ```eval()```. Yes, that sucks, but there is no better way. Moreover the referenced constant may not exist. In that case it is replaced by a ```~~NOT RESOLVED~~``` string.

Runtime constants are not supported.

When the library encounters a duplicate class, function or constant name, it converts the previously created reflection into an "invalid reflection" instance. That means that the parser is unable to distinguish between such classes and it is unable to build a proper class tree for example. And it throws an exception. When you catch this exception and continue to work with the Broker instance, the duplicate classes, functions or constants will have only one reflection and it will be an instance of **Invalid\ReflectionClass**, **Invalid\ReflectionFunction** or **Invalid\ReflectionConstant** respectively.

## Usage

To be able to work with reflections you have to let the library parse the source code first. That is what [TokenReflection\\Broker](https://github.com/Andrewsville/PHP-Token-Reflection/blob/master/library/TokenReflection/Broker.php) does. It walks through the given directories, tokenizes PHP sources and caches reflection objects. Moreover, you cannot just instantiate a reflection class. You have to ask the Broker for the reflection. And once you have a reflection instance, everything works as expected :)

```php
<?php
namespace TokenReflection;

$broker = new Broker(new Broker\Backend\Memory());
$broker->processDirectory('~/lib/Zend_Framework');

$class = $broker->getClass('Zend_Version'); // returns a TokenReflection\ReflectionClass instance
$class = $broker->getClass('Exception');    // returns a TokenReflection\Php\ReflectionClass instance
$class = $broker->getClass('Nonexistent');  // returns a TokenReflection\Dummy\ReflectionClass instance

$function = $broker->getFunction(...);
$constant = $broker->getConstant(...);
```

## Requirements

The library requires PHP 5.3 with the [tokenizer extension](http://cz.php.net/manual/en/book.tokenizer.php) enabled. If you want to process PHAR archives, you will require the [appropriate extension](http://cz.php.net/manual/en/book.phar.php) enabled as well.

## Current status

The current version should support the vast majority of PHP internal reflection features and add many more.

Every release is tested using our testing package (several PHP frameworks and other libraries) and its compatibility is tested on all PHP versions of the 5.3 and 5.4 branch and the actual trunk.
