<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Mail;

use Nette;
use Nette\Utils\Strings;


/**
 * Mail provides functionality to compose and send both text and MIME-compliant multipart email messages.
 *
 * @property   string $subject
 * @property   string $htmlBody
 */
class Message extends MimePart
{
	/** Priority */
	const HIGH = 1,
		NORMAL = 3,
		LOW = 5;

	/** @var array */
	public static $defaultHeaders = [
		'MIME-Version' => '1.0',
		'X-Mailer' => 'Nette Framework',
	];

	/** @var array */
	private $attachments = [];

	/** @var array */
	private $inlines = [];

	/** @var string */
	private $htmlBody = '';


	public function __construct()
	{
		foreach (static::$defaultHeaders as $name => $value) {
			$this->setHeader($name, $value);
		}
		$this->setHeader('Date', date('r'));
	}


	/**
	 * Sets the sender of the message.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return static
	 */
	public function setFrom($email, $name = NULL)
	{
		$this->setHeader('From', $this->formatEmail($email, $name));
		return $this;
	}


	/**
	 * Returns the sender of the message.
	 * @return array
	 */
	public function getFrom()
	{
		return $this->getHeader('From');
	}


	/**
	 * Adds the reply-to address.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return static
	 */
	public function addReplyTo($email, $name = NULL)
	{
		$this->setHeader('Reply-To', $this->formatEmail($email, $name), TRUE);
		return $this;
	}


	/**
	 * Sets the subject of the message.
	 * @param  string
	 * @return static
	 */
	public function setSubject($subject)
	{
		$this->setHeader('Subject', $subject);
		return $this;
	}


	/**
	 * Returns the subject of the message.
	 * @return string|NULL
	 */
	public function getSubject()
	{
		return $this->getHeader('Subject');
	}


	/**
	 * Adds email recipient.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return static
	 */
	public function addTo($email, $name = NULL) // addRecipient()
	{
		$this->setHeader('To', $this->formatEmail($email, $name), TRUE);
		return $this;
	}


	/**
	 * Adds carbon copy email recipient.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return static
	 */
	public function addCc($email, $name = NULL)
	{
		$this->setHeader('Cc', $this->formatEmail($email, $name), TRUE);
		return $this;
	}


	/**
	 * Adds blind carbon copy email recipient.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return static
	 */
	public function addBcc($email, $name = NULL)
	{
		$this->setHeader('Bcc', $this->formatEmail($email, $name), TRUE);
		return $this;
	}


	/**
	 * Formats recipient email.
	 * @param  string
	 * @param  string|NULL
	 * @return array
	 */
	private function formatEmail($email, $name)
	{
		if (!$name && preg_match('#^(.+) +<(.*)>\z#', $email, $matches)) {
			return [$matches[2] => $matches[1]];
		} else {
			return [$email => $name];
		}
	}


	/**
	 * Sets the Return-Path header of the message.
	 * @param  string  email
	 * @return static
	 */
	public function setReturnPath($email)
	{
		$this->setHeader('Return-Path', $email);
		return $this;
	}


	/**
	 * Returns the Return-Path header.
	 * @return string
	 */
	public function getReturnPath()
	{
		return $this->getHeader('Return-Path');
	}


	/**
	 * Sets email priority.
	 * @param  int
	 * @return static
	 */
	public function setPriority($priority)
	{
		$this->setHeader('X-Priority', (int) $priority);
		return $this;
	}


	/**
	 * Returns email priority.
	 * @return int
	 */
	public function getPriority()
	{
		return $this->getHeader('X-Priority');
	}


	/**
	 * Sets HTML body.
	 * @param  string
	 * @param  string
	 * @return static
	 */
	public function setHtmlBody($html, $basePath = NULL)
	{
		$html = (string) $html;

		if ($basePath) {
			$cids = [];
			$matches = Strings::matchAll(
				$html,
				'#
					(<img[^<>]*\s src\s*=\s*
					|<body[^<>]*\s background\s*=\s*
					|<[^<>]+\s style\s*=\s* ["\'][^"\'>]+[:\s] url\(
					|<style[^>]*>[^<]+ [:\s] url\()
					(["\']?)(?![a-z]+:|[/\\#])([^"\'>)\s]+)
					|\[\[ ([\w()+./@~-]+) \]\]
				#ix',
				PREG_OFFSET_CAPTURE
			);
			foreach (array_reverse($matches) as $m) {
				$file = rtrim($basePath, '/\\') . '/' . (isset($m[4]) ? $m[4][0] : urldecode($m[3][0]));
				if (!isset($cids[$file])) {
					$cids[$file] = substr($this->addEmbeddedFile($file)->getHeader('Content-ID'), 1, -1);
				}
				$html = substr_replace($html,
					"{$m[1][0]}{$m[2][0]}cid:{$cids[$file]}",
					$m[0][1], strlen($m[0][0])
				);
			}
		}

		if ($this->getSubject() == NULL) { // intentionally ==
			$html = Strings::replace($html, '#<title>(.+?)</title>#is', function ($m) {
				$this->setSubject(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
			});
		}

		$this->htmlBody = ltrim(str_replace("\r", '', $html), "\n");

		if ($this->getBody() === '' && $html !== '') {
			$this->setBody($this->buildText($html));
		}

		return $this;
	}


	/**
	 * Gets HTML body.
	 * @return string
	 */
	public function getHtmlBody()
	{
		return $this->htmlBody;
	}


	/**
	 * Adds embedded file.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return MimePart
	 */
	public function addEmbeddedFile($file, $content = NULL, $contentType = NULL)
	{
		return $this->inlines[$file] = $this->createAttachment($file, $content, $contentType, 'inline')
			->setHeader('Content-ID', $this->getRandomId());
	}


	/**
	 * Adds inlined Mime Part.
	 * @param  MimePart
	 * @return static
	 */
	public function addInlinePart(MimePart $part)
	{
		$this->inlines[] = $part;
		return $this;
	}


	/**
	 * Adds attachment.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return MimePart
	 */
	public function addAttachment($file, $content = NULL, $contentType = NULL)
	{
		return $this->attachments[] = $this->createAttachment($file, $content, $contentType, 'attachment');
	}


	/**
	 * Gets all email attachments.
	 * @return MimePart[]
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}


	/**
	 * Creates file MIME part.
	 * @param  string
	 * @param  string|NULL
	 * @param  string|NULL
	 * @param  string
	 * @return MimePart
	 */
	private function createAttachment($file, $content, $contentType, $disposition)
	{
		$part = new MimePart;
		if ($content === NULL) {
			$content = @file_get_contents($file); // @ is escalated to exception
			if ($content === FALSE) {
				throw new Nette\FileNotFoundException("Unable to read file '$file'.");
			}
		} else {
			$content = (string) $content;
		}
		$part->setBody($content);
		$part->setContentType($contentType ? $contentType : finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $content));
		$part->setEncoding($contentType && preg_match('#(multipart|message)/#A', $contentType) ? self::ENCODING_8BIT : self::ENCODING_BASE64);
		$part->setHeader('Content-Disposition', $disposition . '; filename="' . Strings::fixEncoding(basename($file)) . '"');
		return $part;
	}


	/********************* building and sending ****************d*g**/


	/**
	 * Returns encoded message.
	 * @return string
	 */
	public function generateMessage()
	{
		return $this->build()->getEncodedMessage();
	}


	/**
	 * Builds email. Does not modify itself, but returns a new object.
	 * @return static
	 */
	protected function build()
	{
		$mail = clone $this;
		$mail->setHeader('Message-ID', $this->getRandomId());

		$cursor = $mail;
		if ($mail->attachments) {
			$tmp = $cursor->setContentType('multipart/mixed');
			$cursor = $cursor->addPart();
			foreach ($mail->attachments as $value) {
				$tmp->addPart($value);
			}
		}

		if ($mail->htmlBody !== '') {
			$tmp = $cursor->setContentType('multipart/alternative');
			$cursor = $cursor->addPart();
			$alt = $tmp->addPart();
			if ($mail->inlines) {
				$tmp = $alt->setContentType('multipart/related');
				$alt = $alt->addPart();
				foreach ($mail->inlines as $value) {
					$tmp->addPart($value);
				}
			}
			$alt->setContentType('text/html', 'UTF-8')
				->setEncoding(preg_match('#[^\n]{990}#', $mail->htmlBody)
					? self::ENCODING_QUOTED_PRINTABLE
					: (preg_match('#[\x80-\xFF]#', $mail->htmlBody) ? self::ENCODING_8BIT : self::ENCODING_7BIT))
				->setBody($mail->htmlBody);
		}

		$text = $mail->getBody();
		$mail->setBody('');
		$cursor->setContentType('text/plain', 'UTF-8')
			->setEncoding(preg_match('#[^\n]{990}#', $text)
				? self::ENCODING_QUOTED_PRINTABLE
				: (preg_match('#[\x80-\xFF]#', $text) ? self::ENCODING_8BIT : self::ENCODING_7BIT))
			->setBody($text);

		return $mail;
	}


	/**
	 * Builds text content.
	 * @param  string
	 * @return string
	 */
	protected function buildText($html)
	{
		$text = Strings::replace($html, [
			'#<(style|script|head).*</\\1>#Uis' => '',
			'#<t[dh][ >]#i' => ' $0',
			'#<a\s[^>]*href=(?|"([^"]+)"|\'([^\']+)\')[^>]*>(.*?)</a>#is' =>  '$2 &lt;$1&gt;',
			'#[\r\n]+#' => ' ',
			'#<(/?p|/?h\d|li|br|/tr)[ >/]#i' => "\n$0",
		]);
		$text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
		$text = Strings::replace($text, '#[ \t]+#', ' ');
		return trim($text);
	}


	/** @return string */
	private function getRandomId()
	{
		return '<' . Nette\Utils\Random::generate() . '@'
			. preg_replace('#[^\w.-]+#', '', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : php_uname('n'))
			. '>';
	}

}
