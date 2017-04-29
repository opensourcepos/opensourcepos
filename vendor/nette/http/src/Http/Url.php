<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Http;

use Nette;


/**
 * URI Syntax (RFC 3986).
 *
 * <pre>
 * scheme  user  password  host  port  basePath   relativeUrl
 *   |      |      |        |      |    |             |
 * /--\   /--\ /------\ /-------\ /--\/--\/----------------------------\
 * http://john:x0y17575@nette.org:8042/en/manual.php?name=param#fragment  <-- absoluteUrl
 *        \__________________________/\____________/^\________/^\______/
 *                     |                     |           |         |
 *                 authority               path        query    fragment
 * </pre>
 *
 * - authority:   [user[:password]@]host[:port]
 * - hostUrl:     http://user:password@nette.org:8042
 * - basePath:    /en/ (everything before relative URI not including the script name)
 * - baseUrl:     http://user:password@nette.org:8042/en/
 * - relativeUrl: manual.php
 *
 * @property   string $scheme
 * @property   string $user
 * @property   string $password
 * @property   string $host
 * @property   int $port
 * @property   string $path
 * @property   string $query
 * @property   string $fragment
 * @property-read string $absoluteUrl
 * @property-read string $authority
 * @property-read string $hostUrl
 * @property-read string $basePath
 * @property-read string $baseUrl
 * @property-read string $relativeUrl
 * @property-read array $queryParameters
 */
class Url implements \JsonSerializable
{
	use Nette\SmartObject;

	/** @var array */
	public static $defaultPorts = [
		'http' => 80,
		'https' => 443,
		'ftp' => 21,
		'news' => 119,
		'nntp' => 119,
	];

	/** @var string */
	private $scheme = '';

	/** @var string */
	private $user = '';

	/** @var string */
	private $password = '';

	/** @var string */
	private $host = '';

	/** @var int|NULL */
	private $port;

	/** @var string */
	private $path = '';

	/** @var array */
	private $query = [];

	/** @var string */
	private $fragment = '';


	/**
	 * @param  string|self
	 * @throws Nette\InvalidArgumentException if URL is malformed
	 */
	public function __construct($url = NULL)
	{
		if (is_string($url)) {
			$p = @parse_url($url); // @ - is escalated to exception
			if ($p === FALSE) {
				throw new Nette\InvalidArgumentException("Malformed or unsupported URI '$url'.");
			}

			$this->scheme = isset($p['scheme']) ? $p['scheme'] : '';
			$this->port = isset($p['port']) ? $p['port'] : NULL;
			$this->host = isset($p['host']) ? rawurldecode($p['host']) : '';
			$this->user = isset($p['user']) ? rawurldecode($p['user']) : '';
			$this->password = isset($p['pass']) ? rawurldecode($p['pass']) : '';
			$this->setPath(isset($p['path']) ? $p['path'] : '');
			$this->setQuery(isset($p['query']) ? $p['query'] : []);
			$this->fragment = isset($p['fragment']) ? rawurldecode($p['fragment']) : '';

		} elseif ($url instanceof self) {
			foreach ($this as $key => $val) {
				$this->$key = $url->$key;
			}
		}
	}


	/**
	 * Sets the scheme part of URI.
	 * @param  string
	 * @return static
	 */
	public function setScheme($value)
	{
		$this->scheme = (string) $value;
		return $this;
	}


	/**
	 * Returns the scheme part of URI.
	 * @return string
	 */
	public function getScheme()
	{
		return $this->scheme;
	}


	/**
	 * Sets the user name part of URI.
	 * @param  string
	 * @return static
	 */
	public function setUser($value)
	{
		$this->user = (string) $value;
		return $this;
	}


	/**
	 * Returns the user name part of URI.
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}


	/**
	 * Sets the password part of URI.
	 * @param  string
	 * @return static
	 */
	public function setPassword($value)
	{
		$this->password = (string) $value;
		return $this;
	}


	/**
	 * Returns the password part of URI.
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}


	/**
	 * Sets the host part of URI.
	 * @param  string
	 * @return static
	 */
	public function setHost($value)
	{
		$this->host = (string) $value;
		$this->setPath($this->path);
		return $this;
	}


	/**
	 * Returns the host part of URI.
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}


	/**
	 * Returns the part of domain.
	 * @return string
	 */
	public function getDomain($level = 2)
	{
		$parts = ip2long($this->host) ? [$this->host] : explode('.', $this->host);
		$parts = $level >= 0 ? array_slice($parts, -$level) : array_slice($parts, 0, $level);
		return implode('.', $parts);
	}


	/**
	 * Sets the port part of URI.
	 * @param  int
	 * @return static
	 */
	public function setPort($value)
	{
		$this->port = (int) $value;
		return $this;
	}


	/**
	 * Returns the port part of URI.
	 * @return int|NULL
	 */
	public function getPort()
	{
		return $this->port
			? $this->port
			: (isset(self::$defaultPorts[$this->scheme]) ? self::$defaultPorts[$this->scheme] : NULL);
	}


	/**
	 * Sets the path part of URI.
	 * @param  string
	 * @return static
	 */
	public function setPath($value)
	{
		$this->path = (string) $value;
		if ($this->host && substr($this->path, 0, 1) !== '/') {
			$this->path = '/' . $this->path;
		}
		return $this;
	}


	/**
	 * Returns the path part of URI.
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}


	/**
	 * Sets the query part of URI.
	 * @param  string|array
	 * @return static
	 */
	public function setQuery($value)
	{
		$this->query = is_array($value) ? $value : self::parseQuery($value);
		return $this;
	}


	/**
	 * Appends the query part of URI.
	 * @param  string|array
	 * @return static
	 */
	public function appendQuery($value)
	{
		$this->query = is_array($value)
			? $value + $this->query
			: self::parseQuery($this->getQuery() . '&' . $value);
		return $this;
	}


	/**
	 * Returns the query part of URI.
	 * @return string
	 */
	public function getQuery()
	{
		return http_build_query($this->query, '', '&', PHP_QUERY_RFC3986);
	}


	/**
	 * @return array
	 */
	public function getQueryParameters()
	{
		return $this->query;
	}


	/**
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public function getQueryParameter($name, $default = NULL)
	{
		return isset($this->query[$name]) ? $this->query[$name] : $default;
	}


	/**
	 * @param string
	 * @param mixed NULL unsets the parameter
	 * @return static
	 */
	public function setQueryParameter($name, $value)
	{
		$this->query[$name] = $value;
		return $this;
	}


	/**
	 * Sets the fragment part of URI.
	 * @param  string
	 * @return static
	 */
	public function setFragment($value)
	{
		$this->fragment = (string) $value;
		return $this;
	}


	/**
	 * Returns the fragment part of URI.
	 * @return string
	 */
	public function getFragment()
	{
		return $this->fragment;
	}


	/**
	 * Returns the entire URI including query string and fragment.
	 * @return string
	 */
	public function getAbsoluteUrl()
	{
		return $this->getHostUrl() . $this->path
			. (($tmp = $this->getQuery()) ? '?' . $tmp : '')
			. ($this->fragment === '' ? '' : '#' . $this->fragment);
	}


	/**
	 * Returns the [user[:pass]@]host[:port] part of URI.
	 * @return string
	 */
	public function getAuthority()
	{
		return $this->host === ''
			? ''
			: ($this->user !== '' && $this->scheme !== 'http' && $this->scheme !== 'https'
				? rawurlencode($this->user) . ($this->password === '' ? '' : ':' . rawurlencode($this->password)) . '@'
				: '')
			. $this->host
			. ($this->port && (!isset(self::$defaultPorts[$this->scheme]) || $this->port !== self::$defaultPorts[$this->scheme])
				? ':' . $this->port
				: '');
	}


	/**
	 * Returns the scheme and authority part of URI.
	 * @return string
	 */
	public function getHostUrl()
	{
		return ($this->scheme ? $this->scheme . ':' : '')
			. (($authority = $this->getAuthority()) || $this->scheme ? '//' . $authority : '');
	}


	/**
	 * Returns the base-path.
	 * @return string
	 */
	public function getBasePath()
	{
		$pos = strrpos($this->path, '/');
		return $pos === FALSE ? '' : substr($this->path, 0, $pos + 1);
	}


	/**
	 * Returns the base-URI.
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->getHostUrl() . $this->getBasePath();
	}


	/**
	 * Returns the relative-URI.
	 * @return string
	 */
	public function getRelativeUrl()
	{
		return (string) substr($this->getAbsoluteUrl(), strlen($this->getBaseUrl()));
	}


	/**
	 * URL comparison.
	 * @param  string|self
	 * @return bool
	 */
	public function isEqual($url)
	{
		$url = new self($url);
		$query = $url->query;
		ksort($query);
		$query2 = $this->query;
		ksort($query2);
		$http = in_array($this->scheme, ['http', 'https'], TRUE);
		return $url->scheme === $this->scheme
			&& !strcasecmp($url->host, $this->host)
			&& $url->getPort() === $this->getPort()
			&& ($http || $url->user === $this->user)
			&& ($http || $url->password === $this->password)
			&& self::unescape($url->path, '%/') === self::unescape($this->path, '%/')
			&& $query === $query2
			&& $url->fragment === $this->fragment;
	}


	/**
	 * Transforms URL to canonical form.
	 * @return static
	 */
	public function canonicalize()
	{
		$this->path = preg_replace_callback(
			'#[^!$&\'()*+,/:;=@%]+#',
			function ($m) { return rawurlencode($m[0]); },
			self::unescape($this->path, '%/')
		);
		$this->host = strtolower($this->host);
		return $this;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getAbsoluteUrl();
	}


	/**
	 * @return string
	 */
	public function jsonSerialize()
	{
		return $this->getAbsoluteUrl();
	}


	/**
	 * Similar to rawurldecode, but preserves reserved chars encoded.
	 * @param  string to decode
	 * @param  string reserved characters
	 * @return string
	 */
	public static function unescape($s, $reserved = '%;/?:@&=+$,')
	{
		// reserved (@see RFC 2396) = ";" | "/" | "?" | ":" | "@" | "&" | "=" | "+" | "$" | ","
		// within a path segment, the characters "/", ";", "=", "?" are reserved
		// within a query component, the characters ";", "/", "?", ":", "@", "&", "=", "+", ",", "$" are reserved.
		if ($reserved !== '') {
			$s = preg_replace_callback(
				'#%(' . substr(chunk_split(bin2hex($reserved), 2, '|'), 0, -1) . ')#i',
				function ($m) { return '%25' . strtoupper($m[1]); },
				$s
			);
		}
		return rawurldecode($s);
	}


	/**
	 * Parses query string.
	 * @return array
	 */
	public static function parseQuery($s)
	{
		parse_str($s, $res);
		return $res;
	}

}
