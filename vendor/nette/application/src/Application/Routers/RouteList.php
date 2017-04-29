<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\Routers;

use Nette;


/**
 * The router broker.
 */
class RouteList extends Nette\Utils\ArrayList implements Nette\Application\IRouter
{
	/** @var array */
	private $cachedRoutes;

	/** @var string|NULL */
	private $module;


	public function __construct($module = NULL)
	{
		$this->module = $module ? $module . ':' : '';
	}


	/**
	 * Maps HTTP request to a Request object.
	 * @return Nette\Application\Request|NULL
	 */
	public function match(Nette\Http\IRequest $httpRequest)
	{
		foreach ($this as $route) {
			$appRequest = $route->match($httpRequest);
			if ($appRequest !== NULL) {
				$name = $appRequest->getPresenterName();
				if (strncmp($name, 'Nette:', 6)) {
					$appRequest->setPresenterName($this->module . $name);
				}
				return $appRequest;
			}
		}
		return NULL;
	}


	/**
	 * Constructs absolute URL from Request object.
	 * @return string|NULL
	 */
	public function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
	{
		if ($this->cachedRoutes === NULL) {
			$this->warmupCache();
		}

		if ($this->module) {
			if (strncmp($tmp = $appRequest->getPresenterName(), $this->module, strlen($this->module)) === 0) {
				$appRequest = clone $appRequest;
				$appRequest->setPresenterName(substr($tmp, strlen($this->module)));
			} else {
				return NULL;
			}
		}

		$presenter = $appRequest->getPresenterName();
		if (!isset($this->cachedRoutes[$presenter])) {
			$presenter = '*';
		}

		foreach ($this->cachedRoutes[$presenter] as $route) {
			$url = $route->constructUrl($appRequest, $refUrl);
			if ($url !== NULL) {
				return $url;
			}
		}

		return NULL;
	}


	public function warmupCache()
	{
		$routes = [];
		$routes['*'] = [];

		foreach ($this as $route) {
			$presenters = $route instanceof Route && is_array($tmp = $route->getTargetPresenters())
				? $tmp
				: array_keys($routes);

			foreach ($presenters as $presenter) {
				if (!isset($routes[$presenter])) {
					$routes[$presenter] = $routes['*'];
				}
				$routes[$presenter][] = $route;
			}
		}

		$this->cachedRoutes = $routes;
	}


	/**
	 * Adds the router.
	 * @param  mixed
	 * @param  Nette\Application\IRouter
	 * @return void
	 */
	public function offsetSet($index, $route)
	{
		if (!$route instanceof Nette\Application\IRouter) {
			throw new Nette\InvalidArgumentException('Argument must be IRouter descendant.');
		}
		parent::offsetSet($index, $route);
	}


	/**
	 * @return string|NULL
	 */
	public function getModule()
	{
		return $this->module;
	}

}
