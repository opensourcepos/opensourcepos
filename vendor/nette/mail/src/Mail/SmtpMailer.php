<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Mail;

use Nette;


/**
 * Sends emails via the SMTP server.
 */
class SmtpMailer implements IMailer
{
	use Nette\SmartObject;

	/** @var resource */
	private $connection;

	/** @var string */
	private $host;

	/** @var int */
	private $port;

	/** @var string */
	private $username;

	/** @var string */
	private $password;

	/** @var string ssl | tls | (empty) */
	private $secure;

	/** @var int */
	private $timeout;

	/** @var resource */
	private $context;

	/** @var bool */
	private $persistent;


	public function __construct(array $options = [])
	{
		if (isset($options['host'])) {
			$this->host = $options['host'];
			$this->port = isset($options['port']) ? (int) $options['port'] : NULL;
		} else {
			$this->host = ini_get('SMTP');
			$this->port = (int) ini_get('smtp_port');
		}
		$this->username = isset($options['username']) ? $options['username'] : '';
		$this->password = isset($options['password']) ? $options['password'] : '';
		$this->secure = isset($options['secure']) ? $options['secure'] : '';
		$this->timeout = isset($options['timeout']) ? (int) $options['timeout'] : 20;
		$this->context = isset($options['context']) ? stream_context_create($options['context']) : stream_context_get_default();
		if (!$this->port) {
			$this->port = $this->secure === 'ssl' ? 465 : 25;
		}
		$this->persistent = !empty($options['persistent']);
	}


	/**
	 * Sends email.
	 * @return void
	 * @throws SmtpException
	 */
	public function send(Message $mail)
	{
		$mail = clone $mail;

		try {
			if (!$this->connection) {
				$this->connect();
			}

			if (($from = $mail->getHeader('Return-Path'))
				|| ($from = key($mail->getHeader('From')))
			) {
				$this->write("MAIL FROM:<$from>", 250);
			}

			foreach (array_merge(
				(array) $mail->getHeader('To'),
				(array) $mail->getHeader('Cc'),
				(array) $mail->getHeader('Bcc')
			) as $email => $name) {
				$this->write("RCPT TO:<$email>", [250, 251]);
			}

			$mail->setHeader('Bcc', NULL);
			$data = $mail->generateMessage();
			$this->write('DATA', 354);
			$data = preg_replace('#^\.#m', '..', $data);
			$this->write($data);
			$this->write('.', 250);

			if (!$this->persistent) {
				$this->write('QUIT', 221);
				$this->disconnect();
			}
		} catch (SmtpException $e) {
			if ($this->connection) {
				$this->disconnect();
			}
			throw $e;
		}
	}


	/**
	 * Connects and authenticates to SMTP server.
	 * @return void
	 */
	protected function connect()
	{
		$this->connection = @stream_socket_client( // @ is escalated to exception
			($this->secure === 'ssl' ? 'ssl://' : '') . $this->host . ':' . $this->port,
			$errno, $error, $this->timeout, STREAM_CLIENT_CONNECT, $this->context
		);
		if (!$this->connection) {
			throw new SmtpException($error, $errno);
		}
		stream_set_timeout($this->connection, $this->timeout, 0);
		$this->read(); // greeting

		$self = isset($_SERVER['HTTP_HOST']) && preg_match('#^[\w.-]+\z#', $_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
		$this->write("EHLO $self");
		$ehloResponse = $this->read();
		if ((int) $ehloResponse !== 250) {
			$this->write("HELO $self", 250);
		}

		if ($this->secure === 'tls') {
			$this->write('STARTTLS', 220);
			if (!stream_socket_enable_crypto($this->connection, TRUE, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
				throw new SmtpException('Unable to connect via TLS.');
			}
			$this->write("EHLO $self", 250);
		}

		if ($this->username != NULL && $this->password != NULL) {
			$authMechanisms = [];
			if (preg_match('~^250[ -]AUTH (.*)$~im', $ehloResponse, $matches)) {
				$authMechanisms = explode(' ', trim($matches[1]));
			}

			if (in_array('PLAIN', $authMechanisms, TRUE)) {
				$credentials = $this->username . "\0" . $this->username . "\0" . $this->password;
				$this->write('AUTH PLAIN ' . base64_encode($credentials), 235, 'PLAIN credentials');
			} else {
				$this->write('AUTH LOGIN', 334);
				$this->write(base64_encode($this->username), 334, 'username');
				$this->write(base64_encode($this->password), 235, 'password');
			}
		}
	}


	/**
	 * Disconnects from SMTP server.
	 * @return void
	 */
	protected function disconnect()
	{
		fclose($this->connection);
		$this->connection = NULL;
	}


	/**
	 * Writes data to server and checks response against expected code if some provided.
	 * @param  string
	 * @param  int|int[] response code
	 * @param  string  error message
	 * @return void
	 */
	protected function write($line, $expectedCode = NULL, $message = NULL)
	{
		fwrite($this->connection, $line . Message::EOL);
		if ($expectedCode) {
			$response = $this->read();
			if (!in_array((int) $response, (array) $expectedCode, TRUE)) {
				throw new SmtpException('SMTP server did not accept ' . ($message ? $message : $line) . ' with error: ' . trim($response));
			}
		}
	}


	/**
	 * Reads response from server.
	 * @return string
	 */
	protected function read()
	{
		$s = '';
		while (($line = fgets($this->connection, 1000)) != NULL) { // intentionally ==
			$s .= $line;
			if (substr($line, 3, 1) === ' ') {
				break;
			}
		}
		return $s;
	}

}
