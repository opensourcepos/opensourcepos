Nette Caching
=============

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/caching.svg)](https://packagist.org/packages/nette/caching)
[![Build Status](https://travis-ci.org/nette/caching.svg?branch=master)](https://travis-ci.org/nette/caching)
[![Coverage Status](https://coveralls.io/repos/github/nette/caching/badge.svg?branch=master)](https://coveralls.io/github/nette/caching?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/caching/v/stable)](https://github.com/nette/caching/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/caching/blob/master/license.md)

Cache accelerates your application by storing data - once hardly retrieved - for future use.

Nette offers a very intuitive API for cache manipulation. After all, you wouldn't expect anything else, right? ;-)
Before we show you the first example, we need to think about place where to store data physically. We can use a database, //Memcached// server,
or the most available storage - hard drive:

```php
// the `temp` directory will be the storage
$storage = new Nette\Caching\Storages\FileStorage('temp');
```

The `Nette\Caching\Storages\FileStorage` storage is very well optimized for performance and in the first place,
it provides full atomicity of operations. What does that mean? When we use cache we can be sure we are not reading a file that is not fully
written yet (by another thread) or that the file gets deleted "under our hands". Using the cache is therefore completely safe.

For manipulation with cache, we use the `Nette\Caching\Cache`:

```php
use Nette\Caching\Cache;

$cache = new Cache($storage); // $storage from the previous example
```

Let's save the contents of the '`$data`' variable under the '`$key`' key:

```php
$cache->save($key, $data);
```

This way, we can read from the cache: (if there is no such item in the cache, the `NULL` value is returned)

```php
$value = $cache->load($key);
if ($value === NULL) ...
```

Method `load()` has second parameter `callable` `$fallback`, which is called when there is no such item in the cache. This callback receives the array *$dependencies* by reference, which you can use for setting expiration rules.

```php
$value = $cache->load($key, function(& $dependencies) {
	// some calculation
	return 15;
});
```

We could delete the item from the cache either by saving NULL or by calling `remove()` method:

```php
$cache->save($key, NULL);
// or
$cache->remove($key);
```

It's possible to save any structure to the cache, not only strings. The same applies for keys.


Web applications typically consist of a number of independent parts, and if they all cache data in the same storage (for example the same directory),
sooner or later there would be collisions in names. Nette Framework solves this by splitting the whole storage to sections
(in the `FileStorage` case using subdirectories). Each part of the application uses it's own section with unique name, therefore no collision can occur.
The name of the section can be passed as the second parameter to the `Cache` class constructor. (These sections are often refered to as //cache namespaces//.)

```php
$cache = new Cache($storage, 'htmlOutput');
```


Caching Function Results
-------------------------

Caching the result of a function or method call can be achieved using the `call()` method:

```php
$name = $cache->call('gethostbyaddr', $ip);
```

The `gethostbyaddr($ip)` will therefore be called only once and next time, only the value from cache will be returned. Of course, for different `$ip`,
different results are cached.

Output Caching
------------------

The output can be cached not only in templates:

```php
if ($block = $cache->start($key)) {

	... printing some data ...

	$block->end(); // save the output to the cache
}
```

In case that the output is already present in the cache, the `start()` method prints it and return `NULL`. Otherwise, it starts to buffer the output and
returns the `$block` object using which we finally save the data to the cache.


Expiration and Invalidation
---------------------------
Here come two problems of storing data in the cache. First, there is a possibility that the storage is completely filled and you cannot save more data inside.
And it may happen that some od the previously saved data will become invalid over time. Therefore, Nette Framework provides a mechanism,
how to limit the validity of data and how to delete them in a controlled way ("to invalidate them", using the framework's terminology).

Data validity is set when saving the data using the third parameter of the `save()` method:

```php
$cache->save($key, $data, array(
	Cache::EXPIRE => '20 minutes', // accepts also seconds or a timestamp.
));
```

It's obvious from the code itself, that we saved the data for next 20 minutes. After this period, the cache will report that there is no record
under the '`$key`' key (ie, will return `NULL`). In fact, you can use any time value that is a valid value for PHP function strToTime().
If we want to extend the validity period with each reading, it can be achieved this way:

```php
$cache->save($key, $data, array(
	Cache::EXPIRE => '20 minutes',
	Cache::SLIDING => TRUE,
));
```

Very handy is also the ability to let the data expire when a particular file is changed or one of several files.
That can be used for stroring data resulting from parsing these files to the cache. For trouble-free functionality, it's recommended to use absolute paths.

```php
$cache->save($key, $data, array(
	Cache::FILES => 'data.yaml', // an array of files can also be specified
));
```

The `Cache::FILES` criteria, of course, can be combined with the time expiration using `Cache::EXPIRE` etc.

The cache can also depend on other cached items. That can be used when we save the whole HTML page in the cache and under different keys, some of its fragments.
As soon as a part changes, the whole page is invalidated.

```php
$cache->save('page', $html, array(
	// will expire if frag1 or frag2 expires
	Cache::ITEMS => array('frag1', 'frag2'),
));
```

Expiration can be controlled even by your own callbacks:

```php
function controlExpiration($val)
{
	return $val;
}

$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('controlExpiration', 1)),
));
```


Expiration Using Tags and Priority
----------------------------------

Very useful invalidation tool are so called //tags//. We can assign a list of tags to each item. For example, suppose we have an HTML page with an article and
comments, which we want to cache. So we specify tags when saving to cache:

```php
$cache->save($articleId, $html, array(
	Cache::TAGS => array("article/$articleId", "comments/$articleId"),
));
```

Now, let's move to the administration. Here we have a form for article editing. Together with saving the article to a database, we call the `clean()` command,
which will delete cached items by tag:

```php
$cache->clean(array(
	Cache::TAGS => array("article/$articleId"),
));
```

And in the place for adding new comments (or editing them), don't forget to invalidate appropriate tag:

```php
$cache->clean(array(
	Cache::TAGS => array("comments/$articleId"),
));
```

What we have achieved? That the HTML cache will invalidate automatically. Whenever someone changes the article with ID of 10, it will force the `article/10`
tag to invalidate and the tagged HTML page in cache is cleared. The same will happen when someone inserts a new comment below the article.

Similar to tags, you can control expiration by priority:

```php
$cache->save($key, $value, array(
	Cache::PRIORITY => 50,
));

// all cached items with priority less than or equal to 100 will be removed.
$cache->clean(array(
	Cache::PRIORITY => 100,
));
```



Cache Storage
--------
In addition to already mentioned `FileStorage`, Nette Framework also provides `MemcachedStorage` which stores
data to the `Memcached` server, and also `MemoryStorage` for storing data in memory for duration of the request.
The special `DevNullStorage`, which does precisely nothing, can be used for testing, when we want to eliminate the influence of caching.

Of course, it's possible to create your own storage. The only requirement is to implement the `IStorage` interface.


Concurrent Caching
------------------

Deleting the cache is a common operation when uploading a new application version to the server. At that moment, however, the server gets pretty hard time,
because it has to build a complete new cache. Retrieving some data can be quite difficult, for example the RobotLoader cache building.
And moreover, if, say, 30 requests come in a short period, the resource consumption is even higher.

The solution is to modify application behaviour so that data are created only by one thread and others are waiting. To do this, specify the value as a callback
or use an anonymous function:

```php
$result = $cache->save($key, function() { // or callback(...)
        return buildData(); // difficult operation
});
```

Framework will ensure that the body of the function will be called only by one thread at once, and other threads will be waiting.
If the thread fails for some reason, another gets chance.
