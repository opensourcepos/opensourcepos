<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;


/**
 * Component is the base class for all Presenter components.
 *
 * Components are persistent objects located on a presenter. They have ability to own
 * other child components, and interact with user. Components have properties
 * for storing their status, and responds to user command.
 *
 * @property-read Presenter $presenter
 * @property-read bool $linkCurrent
 */
abstract class Component extends Nette\ComponentModel\Container implements ISignalReceiver, IStatePersistent, \ArrayAccess
{
	/** @var callable[]  function (self $sender); Occurs when component is attached to presenter */
	public $onAnchor;

	/** @var array */
	protected $params = [];


	/**
	 * Returns the presenter where this component belongs to.
	 * @param  bool   throw exception if presenter doesn't exist?
	 * @return Presenter|NULL
	 */
	public function getPresenter($throw = TRUE)
	{
		return $this->lookup(Presenter::class, $throw);
	}


	/**
	 * Returns a fully-qualified name that uniquely identifies the component
	 * within the presenter hierarchy.
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->lookupPath(Presenter::class, TRUE);
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($presenter)
	{
		if ($presenter instanceof Presenter) {
			$this->loadState($presenter->popGlobalParameters($this->getUniqueId()));
			$this->onAnchor($this);
		}
	}


	/**
	 * @return void
	 */
	protected function validateParent(Nette\ComponentModel\IContainer $parent)
	{
		parent::validateParent($parent);
		$this->monitor(Presenter::class);
	}


	/**
	 * Calls public method if exists.
	 * @param  string
	 * @param  array
	 * @return bool  does method exist?
	 */
	protected function tryCall($method, array $params)
	{
		$rc = $this->getReflection();
		if ($rc->hasMethod($method)) {
			$rm = $rc->getMethod($method);
			if ($rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic()) {
				$this->checkRequirements($rm);
				$rm->invokeArgs($this, $rc->combineArgs($rm, $params));
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * Checks for requirements such as authorization.
	 * @return void
	 */
	public function checkRequirements($element)
	{
	}


	/**
	 * Access to reflection.
	 * @return ComponentReflection
	 */
	public static function getReflection()
	{
		return new ComponentReflection(get_called_class());
	}


	/********************* interface IStatePersistent ****************d*g**/


	/**
	 * Loads state informations.
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		$reflection = $this->getReflection();
		foreach ($reflection->getPersistentParams() as $name => $meta) {
			if (isset($params[$name])) { // NULLs are ignored
				$type = gettype($meta['def']);
				if (!$reflection->convertType($params[$name], $type)) {
					throw new Nette\Application\BadRequestException(sprintf(
						"Value passed to persistent parameter '%s' in %s must be %s, %s given.",
						$name,
						$this instanceof Presenter ? 'presenter ' . $this->getName() : "component '{$this->getUniqueId()}'",
						$type === 'NULL' ? 'scalar' : $type,
						is_object($params[$name]) ? get_class($params[$name]) : gettype($params[$name])
					));
				}
				$this->$name = $params[$name];
			} else {
				$params[$name] = $this->$name;
			}
		}
		$this->params = $params;
	}


	/**
	 * Saves state informations for next request.
	 * @param  array
	 * @param  ComponentReflection (internal, used by Presenter)
	 * @return void
	 */
	public function saveState(array &$params, $reflection = NULL)
	{
		$reflection = $reflection === NULL ? $this->getReflection() : $reflection;
		foreach ($reflection->getPersistentParams() as $name => $meta) {

			if (isset($params[$name])) {
				// injected value

			} elseif (array_key_exists($name, $params)) { // NULLs are skipped
				continue;

			} elseif ((!isset($meta['since']) || $this instanceof $meta['since']) && isset($this->$name)) {
				$params[$name] = $this->$name; // object property value

			} else {
				continue; // ignored parameter
			}

			$type = gettype($meta['def']);
			if (!ComponentReflection::convertType($params[$name], $type)) {
				throw new InvalidLinkException(sprintf(
					"Value passed to persistent parameter '%s' in %s must be %s, %s given.",
					$name,
					$this instanceof Presenter ? 'presenter ' . $this->getName() : "component '{$this->getUniqueId()}'",
					$type === 'NULL' ? 'scalar' : $type,
					is_object($params[$name]) ? get_class($params[$name]) : gettype($params[$name])
				));
			}

			if ($params[$name] === $meta['def'] || ($meta['def'] === NULL && $params[$name] === '')) {
				$params[$name] = NULL; // value transmit is unnecessary
			}
		}
	}


	/**
	 * Returns component param.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public function getParameter($name, $default = NULL)
	{
		if (isset($this->params[$name])) {
			return $this->params[$name];

		} else {
			return $default;
		}
	}


	/**
	 * Returns component parameters.
	 * @return array
	 */
	public function getParameters()
	{
		return $this->params;
	}


	/**
	 * Returns a fully-qualified name that uniquely identifies the parameter.
	 * @param  string
	 * @return string
	 */
	public function getParameterId($name)
	{
		$uid = $this->getUniqueId();
		return $uid === '' ? $name : $uid . self::NAME_SEPARATOR . $name;
	}


	/** @deprecated */
	function getParam($name = NULL, $default = NULL)
	{
		//trigger_error(__METHOD__ . '() is deprecated; use getParameter() instead.', E_USER_DEPRECATED);
		return func_num_args() ? $this->getParameter($name, $default) : $this->getParameters();
	}


	/**
	 * Returns array of classes persistent parameters. They have public visibility and are non-static.
	 * This default implementation detects persistent parameters by annotation @persistent.
	 * @return array
	 */
	public static function getPersistentParams()
	{
		$rc = new \ReflectionClass(get_called_class());
		$params = [];
		foreach ($rc->getProperties(\ReflectionProperty::IS_PUBLIC) as $rp) {
			if (!$rp->isStatic() && ComponentReflection::parseAnnotation($rp, 'persistent')) {
				$params[] = $rp->getName();
			}
		}
		return $params;
	}


	/********************* interface ISignalReceiver ****************d*g**/


	/**
	 * Calls signal handler method.
	 * @param  string
	 * @return void
	 * @throws BadSignalException if there is not handler method
	 */
	public function signalReceived($signal)
	{
		if (!$this->tryCall($this->formatSignalMethod($signal), $this->params)) {
			$class = get_class($this);
			throw new BadSignalException("There is no handler for signal '$signal' in class $class.");
		}
	}


	/**
	 * Formats signal handler method name -> case sensitivity doesn't matter.
	 * @param  string
	 * @return string
	 */
	public static function formatSignalMethod($signal)
	{
		return $signal == NULL ? NULL : 'handle' . $signal; // intentionally ==
	}


	/********************* navigation ****************d*g**/


	/**
	 * Generates URL to presenter, action or signal.
	 * @param  string   destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed
	 * @return string
	 * @throws InvalidLinkException
	 */
	public function link($destination, $args = [])
	{
		try {
			$args = func_num_args() < 3 && is_array($args) ? $args : array_slice(func_get_args(), 1);
			return $this->getPresenter()->createRequest($this, $destination, $args, 'link');

		} catch (InvalidLinkException $e) {
			return $this->getPresenter()->handleInvalidLink($e);
		}
	}


	/**
	 * Returns destination as Link object.
	 * @param  string   destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed
	 * @return Link
	 */
	public function lazyLink($destination, $args = [])
	{
		$args = func_num_args() < 3 && is_array($args) ? $args : array_slice(func_get_args(), 1);
		return new Link($this, $destination, $args);
	}


	/**
	 * Determines whether it links to the current page.
	 * @param  string   destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed
	 * @return bool
	 * @throws InvalidLinkException
	 */
	public function isLinkCurrent($destination = NULL, $args = [])
	{
		if ($destination !== NULL) {
			$args = func_num_args() < 3 && is_array($args) ? $args : array_slice(func_get_args(), 1);
			$this->getPresenter()->createRequest($this, $destination, $args, 'test');
		}
		return $this->getPresenter()->getLastCreatedRequestFlag('current');
	}


	/**
	 * Redirect to another presenter, action or signal.
	 * @param  int      [optional] HTTP error code
	 * @param  string   destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function redirect($code, $destination = NULL, $args = [])
	{
		if (!is_numeric($code)) { // first parameter is optional
			$args = func_num_args() < 3 && is_array($destination) ? $destination : array_slice(func_get_args(), 1);
			$destination = $code;
			$code = NULL;

		} elseif (func_num_args() > 3 || !is_array($args)) {
			$args = array_slice(func_get_args(), 2);
		}

		$presenter = $this->getPresenter();
		$presenter->redirectUrl($presenter->createRequest($this, $destination, $args, 'redirect'), $code);
	}


	/**
	 * Permanently redirects to presenter, action or signal.
	 * @param  string   destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function redirectPermanent($destination = NULL, $args = [])
	{
		$args = func_num_args() < 3 && is_array($args) ? $args : array_slice(func_get_args(), 1);
		$presenter = $this->getPresenter();
		$presenter->redirectUrl(
			$presenter->createRequest($this, $destination, $args, 'redirect'),
			Nette\Http\IResponse::S301_MOVED_PERMANENTLY
		);
	}


	/********************* interface \ArrayAccess ****************d*g**/


	/**
	 * Adds the component to the container.
	 * @param  string  component name
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}


	/**
	 * Returns component specified by name. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return Nette\ComponentModel\IComponent
	 * @throws Nette\InvalidArgumentException
	 */
	public function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}


	/**
	 * Does component specified by name exists?
	 * @param  string  component name
	 * @return bool
	 */
	public function offsetExists($name)
	{
		return $this->getComponent($name, FALSE) !== NULL;
	}


	/**
	 * Removes component from the container.
	 * @param  string  component name
	 * @return void
	 */
	public function offsetUnset($name)
	{
		$component = $this->getComponent($name, FALSE);
		if ($component !== NULL) {
			$this->removeComponent($component);
		}
	}

}
