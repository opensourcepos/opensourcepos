Nette HTTP Component
====================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/http.svg)](https://packagist.org/packages/nette/http)
[![Build Status](https://travis-ci.org/nette/http.svg?branch=master)](https://travis-ci.org/nette/http)
[![Build Status Windows](https://ci.appveyor.com/api/projects/status/github/nette/http?branch=master&svg=true)](https://ci.appveyor.com/project/dg/http/branch/master)
[![Coverage Status](https://coveralls.io/repos/github/nette/http/badge.svg?branch=master)](https://coveralls.io/github/nette/http?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/http/v/stable)](https://github.com/nette/http/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/http/blob/master/license.md)

HTTP request and response are encapsulated in `Nette\Http\Request` and `Nette\Http\Response` objects which offer comfortable API and also act as
sanitization filter.


HTTP Request
-------------

Nette cleans out data sent by user from control and invalid characters.

The URL of the request is available as [api:Nette\Http\UrlScript] instance:

```php
$url = $httpRequest->getUrl();
echo $url;       // e.g. https://nette.org/en/documentation?action=edit
echo $url->host; // nette.org
```

Determine current HTTP method:

```php
echo $httpRequest->getMethod(); // GET, POST, HEAD, PUT

if ($httpRequest->isMethod('GET')) ...
```

Is the connection encrypted (HTTPS)?

```php
echo $httpRequest->isSecured() ? 'yes' : 'no';
```

Is this an AJAX request?

```php
echo $httpRequest->isAjax() ? 'yes' : 'no';
```

What is the user's IP address?

```php
echo $httpRequest->getRemoteAddress(); // user's IP address
echo $httpRequest->getRemoteHost();    // and its DNS translation
```

What URL the user came from? Returned as [Nette\Http\Url |urls] object.

```php
echo $httpRequest->getReferer()->host;
```

Request parameters:

```php
$get = $httpRequest->getQuery();    // array of all URL parameters
$id = $httpRequest->getQuery('id'); // returns GET parameter 'id' (or NULL)

$post = $httpRequest->getPost();    // array of all POST parameters
$id = $httpRequest->getPost('id');  // returns POST parameter 'id' (or NULL)

$cookies = $httpRequest->getCookies(); // array of all cookies
$sessId = $httpRequest->getCookie('sess_id'); // returns the cookie (or NULL)
```

Uploaded files are encapsulated into [api:Nette\Http\FileUpload] objects:

```php
$files = $httpRequest->getFiles(); // array of all uploaded files

$file = $httpRequest->getFile('avatar'); // returns one file
echo $file->getName(); // name of the file sent by user
echo $file->getSanitizedName(); // the name without dangerous characters
```

HTTP headers are also accessible:

```php
// returns associative array of HTTP headers
$headers = $httpRequest->getHeaders();

// returns concrete header (case-insensitive)
$userAgent = $httpRequest->getHeader('User-Agent');
```

A useful method is `detectLanguage()`. You can pass it an array with languages supported by application and it returns the one preferred by browser.
It is not magic, the method just uses the `Accept-Language` header.

```php
// Header sent by browser: Accept-Language: cs,en-us;q=0.8,en;q=0.5,sl;q=0.3

$langs = array('hu', 'pl', 'en'); // languages supported in application

echo $httpRequest->detectLanguage($langs); // en
```


RequestFactory and URL filtering
------------------

Object holding current HTTP request is created by [api:Nette\Http\RequstFactory]. Its behavior can be modified.
It's possible to clean up URLs from characters that can get into them because of poorly implemented comment systems on various other websites by using filters:

```php
$requestFactory = new Nette\Http\RequestFactory;

// remove spaces from path
$requestFactory->addUrlFilter('%20', '', PHP_URL_PATH);

// remove dot, comma or right parenthesis form the end of the URL
$requestFactory->addUrlFilter('[.,)]$');

// clean the path from duplicated slashes (default filter)
$requestFactory->addUrlFilter('/{2,}', '/', PHP_URL_PATH);
```

And then we let the factory generate a new `httpRequest` and we store it in a system container:

```php
// $container is a system container
$container->addService('httpRequest', $requestFactory->createHttpRequest());
```


HTTP response
--------------

Whether it is still possible to send headers or change the status code tells the `isSent()` method. If it returns TRUE,
it won't be possible to send another header or change the status code.

In that case, any attempt to send header or change code invokes `Nette\InvalidStateException`. .[caution]

 [Response status code | http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10] can be sent and retrieved this way:

```php
$httpResponse->setCode(Nette\Http\Response::S404_NOT_FOUND);

echo $httpResponse->getCode(); // 404
```

For better source code readability it is recommended to use predefined constants instead of actual numbers:

```
Http\IResponse::S200_OK
Http\IResponse::S204_NO_CONTENT
Http\IResponse::S300_MULTIPLE_CHOICES
Http\IResponse::S301_MOVED_PERMANENTLY
Http\IResponse::S302_FOUND
Http\IResponse::S303_SEE_OTHER
Http\IResponse::S303_POST_GET
Http\IResponse::S304_NOT_MODIFIED
Http\IResponse::S307_TEMPORARY_REDIRECT
Http\IResponse::S400_BAD_REQUEST
Http\IResponse::S401_UNAUTHORIZED
Http\IResponse::S403_FORBIDDEN
Http\IResponse::S404_NOT_FOUND
Http\IResponse::S410_GONE
Http\IResponse::S500_INTERNAL_SERVER_ERROR
Http\IResponse::S501_NOT_IMPLEMENTED
Http\IResponse::S503_SERVICE_UNAVAILABLE
```

Method `setContentType($type, $charset=NULL)` changes `Content-Type` response header:

```php
$httpResponse->setContentType('text/plain', 'UTF-8');
```

Redirection to another URL is done by `redirect($url, $code=302)` method. Do not forget to terminate the script afterwards!

```php
$httpResponse->redirect('http://example.com');
exit;
```


To set the document expiration date, we can use `setExpiration()` method. The parameter is either text data, number of seconds or a timestamp:

```php
// browser cache expires in one hour
$httpResponse->setExpiration('+ 1 hours');
```

Now we send the HTTP response header:

```php
$httpResponse->setHeader('Pragma', 'no-cache');

// or if we want to send the same header more times with different values
$httpResponse->addHeader('Pragma', 'no-cache');
```

Sent headers are also available:

```php
// returns associative array of headers
$headers = $httpResponse->getHeaders();

// returns concrete header (case-insensitive)
$pragma = $httpResponse->getHeader('Pragma');
```

There are two methods for cookie manipulation: `setCookie()` and `deleteCookie()`.

```php
// setCookie($name, $value, $time, [$path, [$domain, [$secure, [$httpOnly]]]])
$httpResponse->setCookie('lang', 'en', '100 days'); // send cookie

// deleteCookie($name, [$path, [$domain, [$secure]]])
$httpResponse->deleteCookie('lang'); // delete cookie
```

These two methods can take more parameters: `$path` (subdirectory where the cookie will be available),
`$domain` and `$secure`. Their detailed description can be found in PHP manual for [php:setcookie] function.
