[Latte](http://latte.nette.org): amazing template engine for PHP
================================================================

[![Downloads this Month](https://img.shields.io/packagist/dm/latte/latte.svg)](https://packagist.org/packages/latte/latte)
[![Build Status](https://travis-ci.org/nette/latte.svg?branch=v2.3)](https://travis-ci.org/nette/latte)

Latte is a template engine for PHP which eases your work and
ensures the output is protected against vulnerabilities, such as XSS.

**Latte is fast:** it compiles templates to plain optimized PHP code.

**Latte is secure:** it is the first PHP engine introducing content-aware escaping.

**Latte speaks your language:** it has intuitive syntax and helps you to build better websites easily.


Getting Started
===============

Although PHP is originally a templating language, it is not particularly suited for writing templates. Let's have a look at an example of a PHP template that prints an array `$items` as a list:

```php
<?php if ($items): ?>
	<?php $counter = 1 ?>
	<ul>
	<?php foreach ($items as $item): ?>
		<li id="item-<?php echo $counter++ ?>"><?php
		echo htmlSpecialChars(mb_convert_case($item, MB_CASE_TITLE)) ?>
		</li>
	<?php endforeach ?>
	</ul>
<?php endif?>
```

The code is rather confusing. Moreover, we must not forget to call `htmlSpecialChars` function. That's why there are so many different template engines for PHP. One of the best template engines is part of Nette Framework and it is called **Latte**. You'll love it!

The same template as the one above can be written easily in Latte:

```html
<ul n:if="$items">
{foreach $items as $item}
	<li id="item-{$iterator->counter}">{$item|capitalize}</li>
{/foreach}
</ul>
```

As you can see there are two types of macros:

- **macro** in braces, for example `{foreach …}`
- **n:macro**, for example `n:if="…"`

How to render template? Just install Latte (it requires PHP 5.3.1 or later) by [downloading the latest package](https://github.com/nette/latte/releases) or using Composer:

```
php composer.phar require latte/latte
```

and run this code:

```php
$latte = new Latte\Engine;
$latte->setTempDirectory('/path/to/tempdir');
$parameters['items'] = array('one', 'two', 'three');
$latte->render('template.latte', $parameters);
```


Macros
======

You can find detailed description of all the default macros on the [extra page](http://doc.nette.org/en/default-macros). Furthermore, you can make your own macros.

Each pair macro, such as `{if} … {/if}`, operating upon single HTML element can be written in `n:macro` notation. So, it is possible to write the `{foreach}` macro in the same manner:

```html
<ul n:if="$items">
	<li n:foreach="$items as $item">{$item|capitalize}</li>
</ul>
```

With n:macros you can do much more interesting tricks as you will see in a moment.

`{$item|capitalize}` macro which prints the `$item` variable contains so called filter, in this case the `capitalize` filter which makes the first letter of each word uppercase.

Very important feature of Latte is that it **escapes variables by default**. Escaping is needed when printing a variable because we have to convert all the characters which have a special meaning in HTML to other sequences. In case we forget it can lead to a serious security hole called Cross Site Scripting (XSS).

Because of different escaping functions that are needed in different documents and different parts of a page, Latte features a unique technology of Context-Aware Escaping which recognizes the context in which the macro is placed and **chooses the right escaping mode.** You don't have to worry that your coder forgets about it causing you goose bumps because of a security hole. Which is great!

If the `$item` variable stores an HTML code and you want to print it without any alteration you just add the modifier noescape: `{$item|noescape}`. Forgetting the modifier mark won't cause any security holes in spirit of „less code, more security“ principle.

You can still use PHP inside the macros normally, including comments as well. But Latte also extends the PHP syntax with three pleasant features:

1. array can be written as `[1, 2, 3]`, which is the same as `array(1, 2, 3)` in PHP
3. we can omit quotes around the strings consisting of letters, numbers and dashes
2. short condition notation `$a ? 'b'` which is the same as `$a ? 'b' : null` in PHP

For example:

```html
{foreach [a, b, c] as $id} ... {/foreach}

{$cond ? hello}  // prints 'hello' if $cond equals true
```

Latte also has a `{* comment macro *}` which doesn't get printed to the output.


n:macros
========

We showed that n:macros are supposed to be written directly into HTML tags as their special attributes. We also said that every pair macro (e.g. `{if} … {/if}`) can be written in n:macro notation. The macro then corresponds to the HTML element in which it is written:

```html
{var $items = ['I', '♥', 'Nette Framework']}

<p n:foreach="$items as $item">{$item}</p>
```

Prints:

```html
<p>I</p>
<p>♥</p>
<p>Nette Framework</p>
```

By using `inner-` prefix we can alter the behavior so that the macro applies only to the body of the element:

```html
<div n:inner-foreach="$items as $item">
	<p>{$item}</p>
	<hr>
</div>
```

Prints:

```html
<div>
	<p>I</p>
	<hr>
	<p>♥</p>
	<hr>
	<p>Nette Framework</p>
	<hr>
</div>
```

Or by using `tag-` prefix the macro is applied on the HTML tags only:

```html
<p><a href="{$url}" n:tag-if="$url">Title</a></p>
```

Depending on the value of `$url` variable this will print:

```html
// when $url is empty
<p>Title</p>

// when $url equals 'http://nette.org'
<p><a href="http://nette.org">Title</a></p>
```

However, n:macros are not only a shortcut for pair macros, there are some pure n:macros as well, for example the coder's best friend [n:class](http://doc.nette.org/en/default-macros#toc-n-class) macro.


Filters
=======

Latte allows calling filters by using the pipe sign notation (preceding space is allowed):

```html
<h1>{$heading|upper}</h1>
```

Filters (or modifiers) can be chained, in that case they apply in order from left to right:

```html
<h1>{$heading|lower|capitalize}</h1>
```

Parameters are put after the filter name separated by colon or comma:

```html
<h1>{$heading|truncate:20,''}</h1>
```

See the summary of [standard filters](http://doc.nette.org/en/default-filters) and how to make user-defined filters.


In templates we can use functions which change or format the data to a form we want. They are called *filters*. See the [summary of the default filters|default filters].


Filter can be registered by any callback or lambda function:

```php
$latte = new Latte\Engine;
$latte->addFilter('shortify', function ($s) {
	return mb_substr($s, 0, 10); // shortens the text to 10 characters
});
```

In this case it would be better for the filter to get an extra parameter:

```php
$latte->addFilter('shortify', function ($s, $len = 10) {
	return mb_substr($s, 0, $len);
});
```

We call it in a template like this:

```php
<p><?php echo $template->shortify($text, 100); ?></p>
```

Latte simplifies the notation - filters are denoted by the pipe sign, they can be chained (they apply in order from left to right). Parameters are separated by colon or comma:

```html
<p>{$text|shortify:100}</p>
```


Performance
===========

Latte is fast. It compiles the templates to native PHP code and stores them in cache on the disk. So they are as fast as if they would have been written in pure PHP.

The template is automatically recompiled each time we change the source file. While developing you just need to edit the templates in Latte and changes are visible in your browser instantly.


Debugging
=========

With each error or typo you will be informed by the Debugger with all the luxury. The template source code is displayed and the red line marks the error showing error message as well. With just a single click you can open the template in your favorite editor and fix the error at once. Easy peasy!

If you are using an IDE with code stepping you can go through the generated PHP code of the template.



Usability
=========

Latte syntax wasn't invented by engineers but came up from webdesigner's practical requests. We were looking for the friendliest syntax with which you can write even the most problematic constructions comfortably enough. You will be surprised how much help Latte can be.

You can find macros for advanced [layout](http://doc.nette.org/en/default-macros#toc-template-expanding-inheritance) managing, for **template inheritance**, nested blocks and so on. Syntax comes from PHP itself so you don't have to learn anything new and you can leverage your know-how.



Context-Aware Escaping
======================

Although the Cross Site Scripting (XSS) is one of the trivial ways of exploiting a web page it is the most common vulnerability but very serious. It can lead to identity theft and so on. The best defense is consistent escaping of printed data, ie. converting the characters which have a special meaning in the given context.

If the coder omits the escaping a security hole is made. That's why template engines implement automated escaping. The problem is that the web page has different contexts and each has different rules for escaping printed data. A security hole then shows up if the wrong escaping functions are used.

But Latte is sophisticated. It features unique technology of *Context-Aware Escaping* which recognizes the context in which the macro is placed and chooses the right escaping mode. What does that mean?

Latte doesn't need any manual work. **All is done automatically, consistently and correctly.** You don't have to worry about security holes.

Lets see how it works:

```html
<p onclick="alert({$movie})">{$movie}</p>

<script>var movie = {$movie};</script>
```

If `$movie` variable stores `'Amarcord & 8 1/2'` string it generates the following output. Notice different escaping used in HTML and JavaScript and also in `onclick` attribute:


```html
<p onclick="alert(&quot;Amarcord &amp; 8 1\/2&quot;)">Amarcord &amp; 8 1/2</p>

<script>var movie = "Amarcord & 8 1\/2";</script>
```

Thanks to Context-Aware Escaping the template is simple and your application perfectly secured against Cross Site Scripting. You can use PHP variables natively inside the JavaScript!



A pretty output
===============

Sticklers will enjoy the look of the HTML output which Latte generates. All tags are indented as they are supposed to. The code looks like it has been processed with some kind of *HTML code beautifier* :-)
