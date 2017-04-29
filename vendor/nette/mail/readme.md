Nette Mail: Sending E-mails
===========================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/mail.svg)](https://packagist.org/packages/nette/mail)
[![Build Status](https://travis-ci.org/nette/mail.svg?branch=master)](https://travis-ci.org/nette/mail)
[![Coverage Status](https://coveralls.io/repos/github/nette/mail/badge.svg?branch=master)](https://coveralls.io/github/nette/mail?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/mail/v/stable)](https://github.com/nette/mail/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/mail/blob/master/license.md)

Almost every web application needs to send e-mails, whether newsletters or order confirmations. That's why Nette Framework provides necessary tools.

Example of creating an e-mail using `Nette\Mail\Message` class:

```php
use Nette\Mail\Message;

$mail = new Message;
$mail->setFrom('John <john@example.com>')
	->addTo('peter@example.com')
	->addTo('jack@example.com')
	->setSubject('Order Confirmation')
	->setBody("Hello, Your order has been accepted.");
```

All parameters must be encoded in UTF-8.

And sending:

```php
use Nette\Mail\SendmailMailer;

$mailer = new SendmailMailer;
$mailer->send($mail);
```

In addition to specifying recipient with `addTo()`, it's possible to specify recipient of copy with `addCc()` and recipient of blind copy: `addBcc()`.
In all these methods, including `setFrom()`, we can specifiy addressee in three ways:

```php
$mail->setFrom('john.doe@example.com');
$mail->setFrom('john.doe@example.com', 'John Doe');
$mail->setFrom('John Doe <john.doe@example.com>');
```

HTML content can be defined using `setHtmlBody()` method:

```php
$mail->setHTMLBody('<b>Sample HTML</b> <img src="background.gif">');
```

Embedded images can be inserted using `$mail->addEmbeddedFile('background.gif')`, but it is not necessary.
Why? Because Nette Framework finds and inserts all files referenced in the HTML code automatically.
This behavior can be supressed by adding `FALSE` as a second parameter of the `setHtmlBody()` method.

If a HTML e-mail has no plain-text alternative, it will be automatically generated. And if it has no subject set, it will be taken from the `<title>` element.

Of course, it's possible to add attachments to the e-mail:

```php
$mail->addAttachment('example.zip');
```

Can e-mail sending be even easier?


Custom mailer
-------------

Default mailer uses PHP function `mail`. If you need to send mail through a SMTP server, you can use `SmtpMailer`.

```php
$mailer = new Nette\Mail\SmtpMailer([
        'host' => 'smtp.gmail.com',
        'username' => 'john@gmail.com',
        'password' => '*****',
        'secure' => 'ssl',
        'context' =>  [
            'ssl' => [
                'capath' => '/path/to/my/trusted/ca/folder',
             ],
        ],
]);
$mailer->send($mail);
```

You can also create your own mailer - it's a class implementing Nette\Mail\IMailer interface.
