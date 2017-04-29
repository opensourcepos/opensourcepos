<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\Routers;

use Nette;
use Nette\Application;
use Nette\Utils\Strings;


/**
 * The bidirectional route is responsible for mapping
 * HTTP request to a Request object for dispatch and vice-versa.
 */
class Route implements Application\IRouter
{
	use Nette\SmartObject;

	const PRESENTER_KEY = 'presenter';
	const MODULE_KEY = 'module';

	/** @internal url type */
	const HOST = 1,
		PATH = 2,
		RELATIVE = 3;

	/** key used in {@link Route::$styles} or metadata {@link Route::__construct} */
	const VALUE = 'value';
	const PATTERN = 'pattern';
	const FILTER_IN = 'filterIn';
	const FILTER_OUT = 'filterOut';
	const FILTER_TABLE = 'filterTable';
	const FILTER_STRICT = 'filterStrict';

	/** @internal fixity types - how to handle default value? {@link Route::$metadata} */
	const OPTIONAL = 0,
		PATH_OPTIONAL = 1,
		CONSTANT = 2;

	/** @deprecated */
	public static $defaultFlags = 0;

	/** @var array */
	public static $styles = [
		'#' => [ // default style for path parameters
			self::PATTERN => '[^/]+',
			self::FILTER_OUT => [__CLASS__, 'param2path'],
		],
		'?#' => [ // default style for query parameters
		],
		'module' => [
			self::PATTERN => '[a-z][a-z0-9.-]*',
			self::FILTER_IN => [__CLASS__, 'path2presenter'],
			self::FILTER_OUT => [__CLASS__, 'presenter2path'],
		],
		'presenter' => [
			self::PATTERN => '[a-z][a-z0-9.-]*',
			self::FILTER_IN => [__CLASS__, 'path2presenter'],
			self::FILTER_OUT => [__CLASS__, 'presenter2path'],
		],
		'action' => [
			self::PATTERN => '[a-z][a-z0-9-]*',
			self::FILTER_IN => [__CLASS__, 'path2action'],
			self::FILTER_OUT => [__CLASS__, 'action2path'],
		],
		'?module' => [
		],
		'?presenter' => [
		],
		'?action' => [
		],
	];

	/** @var string */
	private $mask;

	/** @var array */
	private $sequence;

	/** @var string  regular expression pattern */
	private $re;

	/** @var string[]  parameter aliases in regular expression */
	private $aliases;

	/** @var array of [value & fixity, filterIn, filterOut] */
	private $metadata = [];

	/** @var array  */
	private $xlat;

	/** @var int HOST, PATH, RELATIVE */
	private $type;

	/** @var string  http | https */
	private $scheme;

	/** @var int */
	private $flags;

	/** @var Nette\Http\Url */
	private $lastRefUrl;

	/** @var string */
	private $lastBaseUrl;


	/**
	 * @param  string  URL mask, e.g. '<presenter>/<action>/<id \d{1,3}>'
	 * @param  array|string|\Closure  default values or metadata or callback for NetteModule\MicroPresenter
	 * @param  int     flags
	 */
	public function __construct($mask, $metadata = [], $flags = 0)
	{
		if (is_string($metadata)) {
			list($presenter, $action) = Nette\Application\Helpers::splitName($metadata);
			if (!$presenter) {
				throw new Nette\InvalidArgumentException("Second argument must be array or string in format Presenter:action, '$metadata' given.");
			}
			$metadata = [self::PRESENTER_KEY => $presenter];
			if ($action !== '') {
				$metadata['action'] = $action;
			}
		} elseif ($metadata instanceof \Closure || $metadata instanceof Nette\Callback) {
			if ($metadata instanceof Nette\Callback) {
				trigger_error('Nette\Callback is deprecated, use Nette\Utils\Callback::closure().', E_USER_DEPRECATED);
			}
			$metadata = [
				self::PRESENTER_KEY => 'Nette:Micro',
				'callback' => $metadata,
			];
		}

		$this->flags = $flags | static::$defaultFlags;
		$this->setMask($mask, $metadata);
		if (static::$defaultFlags) {
			trigger_error('Route::$defaultFlags is deprecated, router by default keeps the used protocol.', E_USER_DEPRECATED);
		} elseif ($flags & self::SECURED) {
			trigger_error('Router::SECURED is deprecated, specify scheme in mask.', E_USER_DEPRECATED);
			$this->scheme = 'https';
		}
	}


	/**
	 * Maps HTTP request to a Request object.
	 * @return Nette\Application\Request|NULL
	 */
	public function match(Nette\Http\IRequest $httpRequest)
	{
		// combine with precedence: mask (params in URL-path), fixity, query, (post,) defaults

		// 1) URL MASK
		$url = $httpRequest->getUrl();
		$re = $this->re;

		if ($this->type === self::HOST) {
			$host = $url->getHost();
			$path = '//' . $host . $url->getPath();
			$parts = ip2long($host) ? [$host] : array_reverse(explode('.', $host));
			$re = strtr($re, [
				'/%basePath%/' => preg_quote($url->getBasePath(), '#'),
				'%tld%' => preg_quote($parts[0], '#'),
				'%domain%' => preg_quote(isset($parts[1]) ? "$parts[1].$parts[0]" : $parts[0], '#'),
				'%sld%' => preg_quote(isset($parts[1]) ? $parts[1] : '', '#'),
				'%host%' => preg_quote($host, '#'),
			]);

		} elseif ($this->type === self::RELATIVE) {
			$basePath = $url->getBasePath();
			if (strncmp($url->getPath(), $basePath, strlen($basePath)) !== 0) {
				return NULL;
			}
			$path = (string) substr($url->getPath(), strlen($basePath));

		} else {
			$path = $url->getPath();
		}

		if ($path !== '') {
			$path = rtrim(rawurldecode($path), '/') . '/';
		}

		if (!$matches = Strings::match($path, $re)) {
			// stop, not matched
			return NULL;
		}

		// assigns matched values to parameters
		$params = [];
		foreach ($matches as $k => $v) {
			if (is_string($k) && $v !== '') {
				$params[$this->aliases[$k]] = $v;
			}
		}


		// 2) CONSTANT FIXITY
		foreach ($this->metadata as $name => $meta) {
			if (!isset($params[$name]) && isset($meta['fixity']) && $meta['fixity'] !== self::OPTIONAL) {
				$params[$name] = NULL; // cannot be overwriten in 3) and detected by isset() in 4)
			}
		}


		// 3) QUERY
		if ($this->xlat) {
			$params += self::renameKeys($httpRequest->getQuery(), array_flip($this->xlat));
		} else {
			$params += $httpRequest->getQuery();
		}


		// 4) APPLY FILTERS & FIXITY
		foreach ($this->metadata as $name => $meta) {
			if (isset($params[$name])) {
				if (!is_scalar($params[$name])) {

				} elseif (isset($meta[self::FILTER_TABLE][$params[$name]])) { // applies filterTable only to scalar parameters
					$params[$name] = $meta[self::FILTER_TABLE][$params[$name]];

				} elseif (isset($meta[self::FILTER_TABLE]) && !empty($meta[self::FILTER_STRICT])) {
					return NULL; // rejected by filterTable

				} elseif (isset($meta[self::FILTER_IN])) { // applies filterIn only to scalar parameters
					$params[$name] = call_user_func($meta[self::FILTER_IN], (string) $params[$name]);
					if ($params[$name] === NULL && !isset($meta['fixity'])) {
						return NULL; // rejected by filter
					}
				}

			} elseif (isset($meta['fixity'])) {
				$params[$name] = $meta[self::VALUE];
			}
		}

		if (isset($this->metadata[NULL][self::FILTER_IN])) {
			$params = call_user_func($this->metadata[NULL][self::FILTER_IN], $params);
			if ($params === NULL) {
				return NULL;
			}
		}

		// 5) BUILD Request
		if (!isset($params[self::PRESENTER_KEY])) {
			throw new Nette\InvalidStateException('Missing presenter in route definition.');
		} elseif (!is_string($params[self::PRESENTER_KEY])) {
			return NULL;
		}
		$presenter = $params[self::PRESENTER_KEY];
		unset($params[self::PRESENTER_KEY]);

		if (isset($this->metadata[self::MODULE_KEY])) {
			$presenter = (isset($params[self::MODULE_KEY]) ? $params[self::MODULE_KEY] . ':' : '') . $presenter;
			unset($params[self::MODULE_KEY]);
		}

		return new Application\Request(
			$presenter,
			$httpRequest->getMethod(),
			$params,
			$httpRequest->getPost(),
			$httpRequest->getFiles(),
			[Application\Request::SECURED => $httpRequest->isSecured()]
		);
	}


	/**
	 * Constructs absolute URL from Request object.
	 * @return string|NULL
	 */
	public function constructUrl(Application\Request $appRequest, Nette\Http\Url $refUrl)
	{
		if ($this->flags & self::ONE_WAY) {
			return NULL;
		}

		$params = $appRequest->getParameters();
		$metadata = $this->metadata;

		$presenter = $appRequest->getPresenterName();
		$params[self::PRESENTER_KEY] = $presenter;

		if (isset($metadata[self::MODULE_KEY])) { // try split into module and [submodule:]presenter parts
			$module = $metadata[self::MODULE_KEY];
			if (isset($module['fixity']) && strncmp($presenter, $module[self::VALUE] . ':', strlen($module[self::VALUE]) + 1) === 0) {
				$a = strlen($module[self::VALUE]);
			} else {
				$a = strrpos($presenter, ':');
			}
			if ($a === FALSE) {
				$params[self::MODULE_KEY] = isset($module[self::VALUE]) ? '' : NULL;
			} else {
				$params[self::MODULE_KEY] = substr($presenter, 0, $a);
				$params[self::PRESENTER_KEY] = substr($presenter, $a + 1);
			}
		}

		if (isset($metadata[NULL][self::FILTER_OUT])) {
			$params = call_user_func($metadata[NULL][self::FILTER_OUT], $params);
			if ($params === NULL) {
				return NULL;
			}
		}

		foreach ($metadata as $name => $meta) {
			if (!isset($params[$name])) {
				continue; // retains NULL values
			}

			if (isset($meta['fixity'])) {
				if ($params[$name] === FALSE) {
					$params[$name] = '0';
				} elseif (is_scalar($params[$name])) {
					$params[$name] = (string) $params[$name];
				}

				if ($params[$name] === $meta[self::VALUE]) { // remove default values; NULL values are retain
					unset($params[$name]);
					continue;

				} elseif ($meta['fixity'] === self::CONSTANT) {
					return NULL; // missing or wrong parameter '$name'
				}
			}

			if (is_scalar($params[$name]) && isset($meta['filterTable2'][$params[$name]])) {
				$params[$name] = $meta['filterTable2'][$params[$name]];

			} elseif (isset($meta['filterTable2']) && !empty($meta[self::FILTER_STRICT])) {
				return NULL;

			} elseif (isset($meta[self::FILTER_OUT])) {
				$params[$name] = call_user_func($meta[self::FILTER_OUT], $params[$name]);
			}

			if (isset($meta[self::PATTERN]) && !preg_match($meta[self::PATTERN], rawurldecode($params[$name]))) {
				return NULL; // pattern not match
			}
		}

		// compositing path
		$sequence = $this->sequence;
		$brackets = [];
		$required = NULL; // NULL for auto-optional
		$url = '';
		$i = count($sequence) - 1;
		do {
			$url = $sequence[$i] . $url;
			if ($i === 0) {
				break;
			}
			$i--;

			$name = $sequence[$i]; $i--; // parameter name

			if ($name === ']') { // opening optional part
				$brackets[] = $url;

			} elseif ($name[0] === '[') { // closing optional part
				$tmp = array_pop($brackets);
				if ($required < count($brackets) + 1) { // is this level optional?
					if ($name !== '[!') { // and not "required"-optional
						$url = $tmp;
					}
				} else {
					$required = count($brackets);
				}

			} elseif ($name[0] === '?') { // "foo" parameter
				continue;

			} elseif (isset($params[$name]) && $params[$name] != '') { // intentionally ==
				$required = count($brackets); // make this level required
				$url = $params[$name] . $url;
				unset($params[$name]);

			} elseif (isset($metadata[$name]['fixity'])) { // has default value?
				if ($required === NULL && !$brackets) { // auto-optional
					$url = '';
				} else {
					$url = $metadata[$name]['defOut'] . $url;
				}

			} else {
				return NULL; // missing parameter '$name'
			}
		} while (TRUE);

		$scheme = $this->scheme ?: $refUrl->getScheme();

		if ($this->type === self::HOST) {
			$host = $refUrl->getHost();
			$parts = ip2long($host) ? [$host] : array_reverse(explode('.', $host));
			$url = strtr($url, [
				'/%basePath%/' => $refUrl->getBasePath(),
				'%tld%' => $parts[0],
				'%domain%' => isset($parts[1]) ? "$parts[1].$parts[0]" : $parts[0],
				'%sld%' => isset($parts[1]) ? $parts[1] : '',
				'%host%' => $host,
			]);
			$url = $scheme . ':' . $url;
		} else {
			if ($this->lastRefUrl !== $refUrl) {
				$basePath = ($this->type === self::RELATIVE ? $refUrl->getBasePath() : '');
				$this->lastBaseUrl = $scheme . '://' . $refUrl->getAuthority() . $basePath;
				$this->lastRefUrl = $refUrl;
			}
			$url = $this->lastBaseUrl . $url;
		}

		if (strpos($url, '//', strlen($scheme) + 3) !== FALSE) {
			return NULL;
		}

		// build query string
		if ($this->xlat) {
			$params = self::renameKeys($params, $this->xlat);
		}

		$sep = ini_get('arg_separator.input');
		$query = http_build_query($params, '', $sep ? $sep[0] : '&');
		if ($query != '') { // intentionally ==
			$url .= '?' . $query;
		}

		return $url;
	}


	/**
	 * Parse mask and array of default values; initializes object.
	 * @param  string
	 * @param  array
	 * @return void
	 */
	private function setMask($mask, array $metadata)
	{
		$this->mask = $mask;

		// detect '//host/path' vs. '/abs. path' vs. 'relative path'
		if (preg_match('#(?:(https?):)?(//.*)#A', $mask, $m)) {
			$this->type = self::HOST;
			list(, $this->scheme, $mask) = $m;

		} elseif (substr($mask, 0, 1) === '/') {
			$this->type = self::PATH;

		} else {
			$this->type = self::RELATIVE;
		}

		foreach ($metadata as $name => $meta) {
			if (!is_array($meta)) {
				$metadata[$name] = $meta = [self::VALUE => $meta];
			}

			if (array_key_exists(self::VALUE, $meta)) {
				if (is_scalar($meta[self::VALUE])) {
					$metadata[$name][self::VALUE] = (string) $meta[self::VALUE];
				}
				$metadata[$name]['fixity'] = self::CONSTANT;
			}
		}

		if (strpbrk($mask, '?<>[]') === FALSE) {
			$this->re = '#' . preg_quote($mask, '#') . '/?\z#A';
			$this->sequence = [$mask];
			$this->metadata = $metadata;
			return;
		}

		// PARSE MASK
		// <parameter-name[=default] [pattern]> or [ or ] or ?...
		$parts = Strings::split($mask, '/<([^<>= ]+)(=[^<> ]*)? *([^<>]*)>|(\[!?|\]|\s*\?.*)/');

		$this->xlat = [];
		$i = count($parts) - 1;

		// PARSE QUERY PART OF MASK
		if (isset($parts[$i - 1]) && substr(ltrim($parts[$i - 1]), 0, 1) === '?') {
			// name=<parameter-name [pattern]>
			$matches = Strings::matchAll($parts[$i - 1], '/(?:([a-zA-Z0-9_.-]+)=)?<([^> ]+) *([^>]*)>/');

			foreach ($matches as list(, $param, $name, $pattern)) { // $pattern is not used
				if (isset(static::$styles['?' . $name])) {
					$meta = static::$styles['?' . $name];
				} else {
					$meta = static::$styles['?#'];
				}

				if (isset($metadata[$name])) {
					$meta = $metadata[$name] + $meta;
				}

				if (array_key_exists(self::VALUE, $meta)) {
					$meta['fixity'] = self::OPTIONAL;
				}

				unset($meta['pattern']);
				$meta['filterTable2'] = empty($meta[self::FILTER_TABLE]) ? NULL : array_flip($meta[self::FILTER_TABLE]);

				$metadata[$name] = $meta;
				if ($param !== '') {
					$this->xlat[$name] = $param;
				}
			}
			$i -= 5;
		}

		// PARSE PATH PART OF MASK
		$brackets = 0; // optional level
		$re = '';
		$sequence = [];
		$autoOptional = TRUE;
		$aliases = [];
		do {
			$part = $parts[$i]; // part of path
			if (strpbrk($part, '<>') !== FALSE) {
				throw new Nette\InvalidArgumentException("Unexpected '$part' in mask '$mask'.");
			}
			array_unshift($sequence, $part);
			$re = preg_quote($part, '#') . $re;
			if ($i === 0) {
				break;
			}
			$i--;

			$part = $parts[$i]; // [ or ]
			if ($part === '[' || $part === ']' || $part === '[!') {
				$brackets += $part[0] === '[' ? -1 : 1;
				if ($brackets < 0) {
					throw new Nette\InvalidArgumentException("Unexpected '$part' in mask '$mask'.");
				}
				array_unshift($sequence, $part);
				$re = ($part[0] === '[' ? '(?:' : ')?') . $re;
				$i -= 4;
				continue;
			}

			$pattern = trim($parts[$i]); $i--; // validation condition (as regexp)
			$default = $parts[$i]; $i--; // default value
			$name = $parts[$i]; $i--; // parameter name
			array_unshift($sequence, $name);

			if ($name[0] === '?') { // "foo" parameter
				$name = substr($name, 1);
				$re = $pattern ? '(?:' . preg_quote($name, '#') . "|$pattern)$re" : preg_quote($name, '#') . $re;
				$sequence[1] = $name . $sequence[1];
				continue;
			}

			// pattern, condition & metadata
			if (isset(static::$styles[$name])) {
				$meta = static::$styles[$name];
			} else {
				$meta = static::$styles['#'];
			}

			if (isset($metadata[$name])) {
				$meta = $metadata[$name] + $meta;
			}

			if ($pattern == '' && isset($meta[self::PATTERN])) {
				$pattern = $meta[self::PATTERN];
			}

			if ($default !== '') {
				$meta[self::VALUE] = (string) substr($default, 1);
				$meta['fixity'] = self::PATH_OPTIONAL;
			}

			$meta['filterTable2'] = empty($meta[self::FILTER_TABLE]) ? NULL : array_flip($meta[self::FILTER_TABLE]);
			if (array_key_exists(self::VALUE, $meta)) {
				if (isset($meta['filterTable2'][$meta[self::VALUE]])) {
					$meta['defOut'] = $meta['filterTable2'][$meta[self::VALUE]];

				} elseif (isset($meta[self::FILTER_OUT])) {
					$meta['defOut'] = call_user_func($meta[self::FILTER_OUT], $meta[self::VALUE]);

				} else {
					$meta['defOut'] = $meta[self::VALUE];
				}
			}
			$meta[self::PATTERN] = "#(?:$pattern)\\z#A";

			// include in expression
			$aliases['p' . $i] = $name;
			$re = '(?P<p' . $i . '>(?U)' . $pattern . ')' . $re;
			if ($brackets) { // is in brackets?
				if (!isset($meta[self::VALUE])) {
					$meta[self::VALUE] = $meta['defOut'] = NULL;
				}
				$meta['fixity'] = self::PATH_OPTIONAL;

			} elseif (!$autoOptional) {
				unset($meta['fixity']);

			} elseif (isset($meta['fixity'])) { // auto-optional
				$re = '(?:' . $re . ')?';
				$meta['fixity'] = self::PATH_OPTIONAL;

			} else {
				$autoOptional = FALSE;
			}

			$metadata[$name] = $meta;
		} while (TRUE);

		if ($brackets) {
			throw new Nette\InvalidArgumentException("Missing '[' in mask '$mask'.");
		}

		$this->aliases = $aliases;
		$this->re = '#' . $re . '/?\z#A';
		$this->metadata = $metadata;
		$this->sequence = $sequence;
	}


	/**
	 * Returns mask.
	 * @return string
	 */
	public function getMask()
	{
		return $this->mask;
	}


	/**
	 * Returns default values.
	 * @return array
	 */
	public function getDefaults()
	{
		$defaults = [];
		foreach ($this->metadata as $name => $meta) {
			if (isset($meta['fixity'])) {
				$defaults[$name] = $meta[self::VALUE];
			}
		}
		return $defaults;
	}


	/**
	 * Returns flags.
	 * @return int
	 */
	public function getFlags()
	{
		return $this->flags;
	}


	/********************* Utilities ****************d*g**/


	/**
	 * Proprietary cache aim.
	 * @internal
	 * @return string[]|NULL
	 */
	public function getTargetPresenters()
	{
		if ($this->flags & self::ONE_WAY) {
			return [];
		}

		$m = $this->metadata;
		$module = '';

		if (isset($m[self::MODULE_KEY])) {
			if (isset($m[self::MODULE_KEY]['fixity']) && $m[self::MODULE_KEY]['fixity'] === self::CONSTANT) {
				$module = $m[self::MODULE_KEY][self::VALUE] . ':';
			} else {
				return NULL;
			}
		}

		if (isset($m[self::PRESENTER_KEY]['fixity']) && $m[self::PRESENTER_KEY]['fixity'] === self::CONSTANT) {
			return [$module . $m[self::PRESENTER_KEY][self::VALUE]];
		}
		return NULL;
	}


	/**
	 * Rename keys in array.
	 * @param  array
	 * @param  array
	 * @return array
	 */
	private static function renameKeys($arr, $xlat)
	{
		if (empty($xlat)) {
			return $arr;
		}

		$res = [];
		$occupied = array_flip($xlat);
		foreach ($arr as $k => $v) {
			if (isset($xlat[$k])) {
				$res[$xlat[$k]] = $v;

			} elseif (!isset($occupied[$k])) {
				$res[$k] = $v;
			}
		}
		return $res;
	}


	/********************* Inflectors ****************d*g**/


	/**
	 * camelCaseAction name -> dash-separated.
	 * @param  string
	 * @return string
	 */
	private static function action2path($s)
	{
		$s = preg_replace('#(.)(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		$s = rawurlencode($s);
		return $s;
	}


	/**
	 * dash-separated -> camelCaseAction name.
	 * @param  string
	 * @return string
	 */
	private static function path2action($s)
	{
		$s = preg_replace('#-(?=[a-z])#', ' ', $s);
		$s = lcfirst(ucwords($s));
		$s = str_replace(' ', '', $s);
		return $s;
	}


	/**
	 * PascalCase:Presenter name -> dash-and-dot-separated.
	 * @param  string
	 * @return string
	 */
	private static function presenter2path($s)
	{
		$s = strtr($s, ':', '.');
		$s = preg_replace('#([^.])(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		$s = rawurlencode($s);
		return $s;
	}


	/**
	 * dash-and-dot-separated -> PascalCase:Presenter name.
	 * @param  string
	 * @return string
	 */
	private static function path2presenter($s)
	{
		$s = preg_replace('#([.-])(?=[a-z])#', '$1 ', $s);
		$s = ucwords($s);
		$s = str_replace('. ', ':', $s);
		$s = str_replace('- ', '', $s);
		return $s;
	}


	/**
	 * Url encode.
	 * @param  string
	 * @return string
	 */
	private static function param2path($s)
	{
		return str_replace('%2F', '/', rawurlencode($s));
	}

}
