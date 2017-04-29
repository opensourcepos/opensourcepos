Nette Component Model
=====================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/component-model.svg)](https://packagist.org/packages/nette/component-model)
[![Build Status](https://travis-ci.org/nette/component-model.svg?branch=master)](https://travis-ci.org/nette/component-model)
[![Coverage Status](https://coveralls.io/repos/github/nette/component-model/badge.svg?branch=master)](https://coveralls.io/github/nette/component-model?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/component-model/v/stable)](https://github.com/nette/component-model/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/component-model/blob/master/license.md)

Components are the foundation of reusable code. They make your work easier and allow you to profit from community work. Components are wonderful.
Nette Framework introduces several classes and interfaces for all these types of components.

Object inheritance allows us to have a hierarchic structure of classes like in real world. We can create new classes by extending. These extended classes are descendants of the original class and inherit its parameters and methods. Extended class can add its own parameters and methods to the inherited ones.

Knowledge class hierarchy is required for proper understanding how things work.

```
Nette\Object
   |
   +- Nette\ComponentModel\Component  { IComponent }
      |
      +- Nette\ComponentModel\Container  { IContainer }
         |
         +- ....
```



Delayed composition
-------------------

Component model in Nette offers very dynamical tree workflow (components can be removed, added, moved). It would be a mistake to rely on knowing parent (in constructor) when the component is created. In most cases, parent is not not known when the component is created.

```php
$control = new NewsControl;
// ...
$parent->addComponent($control, 'shortNews');
```


Monitoring changes
------------------

How to find out when was component added to presenter's tree? Watching the change of parent is not enough because a parent of parent might have been added to presenter. Method `monitor($type)` is here to help. Every component can monitor any number of classes and interfaces. Adding or removing is reported by invoking method `attached($obj)` (`detached($obj)` respectivelly), where `$obj` is the object of monitored class.

An example: Class `UploadControl`, representing form element for uploading files in `Nette\Forms`, has to set form's attribute `enctype` to value `multipart/form-data`. But in the time of the creation of the object it does not have to be attached to any form. When to modify the form? The solution is simple - we create a request for monitoring in the constructor:

```php
class UploadControl extends Nette\Forms\Controls\BaseControl
{
    public function __construct($label)
    {
        $this->monitor(Nette\Forms\Form::class);
        // ...
    }

    // ...
}
```

and method `attached` is called when the form is available:

```php
protected function attached($form)
{
    parent::attached($form);

    if ($form instanceof Nette\Forms\Form) {
        $form->getElementPrototype()->enctype = 'multipart/form-data';
    }
}
```

Monitoring and lookup of components or paths using `lookup` is **very precisely optimized for maximal performance**.


Iterating over children
-----------------------

There is a method `getComponents($deep = FALSE, $type = NULL)` for that. First parameter determines if the components should be looked up in depth (recursively). With `TRUE`, it not only iterates all it's children, but also all chilren of it's children, etc. Second parameter servers as an optional filter by class or interface.

For example, this is the way how validation of forms is "internally"((this is done by framework itself, you don't have to call it explicitly)) performed:

```php
$valid = TRUE;
foreach ($form->getComponents(TRUE, Nette\Forms\IControl::class) as $control) {
    if (!$control->getRules()->validate()) {
        $valid = FALSE;
        break;
    }
}
```
