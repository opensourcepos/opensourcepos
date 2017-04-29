<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\ComponentModel;

use Nette;


/**
 * Component is the base class for all components.
 *
 * Components are objects implementing IComponent. They has parent component and own name.
 *
 * @property-read string $name
 * @property-read IContainer|NULL $parent
 */
abstract class Component implements IComponent
{
	use Nette\SmartObject;

	/** @var IContainer */
	private $parent;

	/** @var string */
	private $name;

	/** @var array of [type => [obj, depth, path, is_monitored?]] */
	private $monitors = [];


	public function __construct()
	{
		list($parent, $name) = func_get_args() + [NULL, NULL];
		if ($parent !== NULL) {
			$parent->addComponent($this, $name);

		} elseif (is_string($name)) {
			$this->name = $name;
		}
	}


	/**
	 * Lookup hierarchy for component specified by class or interface name.
	 * @param  string class/interface type
	 * @param  bool   throw exception if component doesn't exist?
	 * @return IComponent
	 */
	public function lookup($type, $need = TRUE)
	{
		if (!isset($this->monitors[$type])) { // not monitored or not processed yet
			$obj = $this->parent;
			$path = self::NAME_SEPARATOR . $this->name;
			$depth = 1;
			while ($obj !== NULL) {
				$parent = $obj->getParent();
				if ($type ? $obj instanceof $type : $parent === NULL) {
					break;
				}
				$path = self::NAME_SEPARATOR . $obj->getName() . $path;
				$depth++;
				$obj = $parent; // IComponent::getParent()
				if ($obj === $this) {
					$obj = NULL; // prevent cycling
				}
			}

			if ($obj) {
				$this->monitors[$type] = [$obj, $depth, substr($path, 1), FALSE];

			} else {
				$this->monitors[$type] = [NULL, NULL, NULL, FALSE]; // not found
			}
		}

		if ($need && $this->monitors[$type][0] === NULL) {
			throw new Nette\InvalidStateException("Component '$this->name' is not attached to '$type'.");
		}

		return $this->monitors[$type][0];
	}


	/**
	 * Lookup for component specified by class or interface name. Returns backtrace path.
	 * A path is the concatenation of component names separated by self::NAME_SEPARATOR.
	 * @param  string class/interface type
	 * @param  bool   throw exception if component doesn't exist?
	 * @return string
	 */
	public function lookupPath($type = NULL, $need = TRUE)
	{
		$this->lookup($type, $need);
		return $this->monitors[$type][2];
	}


	/**
	 * Starts monitoring.
	 * @param  string class/interface type
	 * @return void
	 */
	public function monitor($type)
	{
		if (empty($this->monitors[$type][3])) {
			if ($obj = $this->lookup($type, FALSE)) {
				$this->attached($obj);
			}
			$this->monitors[$type][3] = TRUE; // mark as monitored
		}
	}


	/**
	 * Stops monitoring.
	 * @param  string class/interface type
	 * @return void
	 */
	public function unmonitor($type)
	{
		unset($this->monitors[$type]);
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  IComponent
	 * @return void
	 */
	protected function attached($obj)
	{
	}


	/**
	 * This method will be called before the component (or component's parent)
	 * becomes detached from a monitored object. Do not call this method yourself.
	 * @param  IComponent
	 * @return void
	 */
	protected function detached($obj)
	{
	}


	/********************* interface IComponent ****************d*g**/


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Returns the container if any.
	 * @return IContainer|NULL
	 */
	public function getParent()
	{
		return $this->parent;
	}


	/**
	 * Sets the parent of this component. This method is managed by containers and should
	 * not be called by applications
	 * @param  IContainer  New parent or null if this component is being removed from a parent
	 * @param  string
	 * @return self
	 * @throws Nette\InvalidStateException
	 * @internal
	 */
	public function setParent(IContainer $parent = NULL, $name = NULL)
	{
		if ($parent === NULL && $this->parent === NULL && $name !== NULL) {
			$this->name = $name; // just rename
			return $this;

		} elseif ($parent === $this->parent && $name === NULL) {
			return $this; // nothing to do
		}

		// A component cannot be given a parent if it already has a parent.
		if ($this->parent !== NULL && $parent !== NULL) {
			throw new Nette\InvalidStateException("Component '$this->name' already has a parent.");
		}

		// remove from parent?
		if ($parent === NULL) {
			$this->refreshMonitors(0);
			$this->parent = NULL;

		} else { // add to parent
			$this->validateParent($parent);
			$this->parent = $parent;
			if ($name !== NULL) {
				$this->name = $name;
			}

			$tmp = [];
			$this->refreshMonitors(0, $tmp);
		}
		return $this;
	}


	/**
	 * Is called by a component when it is about to be set new parent. Descendant can
	 * override this method to disallow a parent change by throwing an Nette\InvalidStateException
	 * @return void
	 * @throws Nette\InvalidStateException
	 */
	protected function validateParent(IContainer $parent)
	{
	}


	/**
	 * Refreshes monitors.
	 * @param  int
	 * @param  array|NULL (array = attaching, NULL = detaching)
	 * @param  array
	 * @return void
	 */
	private function refreshMonitors($depth, & $missing = NULL, & $listeners = [])
	{
		if ($this instanceof IContainer) {
			foreach ($this->getComponents() as $component) {
				if ($component instanceof self) {
					$component->refreshMonitors($depth + 1, $missing, $listeners);
				}
			}
		}

		if ($missing === NULL) { // detaching
			foreach ($this->monitors as $type => $rec) {
				if (isset($rec[1]) && $rec[1] > $depth) {
					if ($rec[3]) { // monitored
						$this->monitors[$type] = [NULL, NULL, NULL, TRUE];
						$listeners[] = [$this, $rec[0]];
					} else { // not monitored, just randomly cached
						unset($this->monitors[$type]);
					}
				}
			}

		} else { // attaching
			foreach ($this->monitors as $type => $rec) {
				if (isset($rec[0])) { // is in cache yet
					continue;

				} elseif (!$rec[3]) { // not monitored, just randomly cached
					unset($this->monitors[$type]);

				} elseif (isset($missing[$type])) { // known from previous lookup
					$this->monitors[$type] = [NULL, NULL, NULL, TRUE];

				} else {
					$this->monitors[$type] = NULL; // forces re-lookup
					if ($obj = $this->lookup($type, FALSE)) {
						$listeners[] = [$this, $obj];
					} else {
						$missing[$type] = TRUE;
					}
					$this->monitors[$type][3] = TRUE; // mark as monitored
				}
			}
		}

		if ($depth === 0) { // call listeners
			$method = $missing === NULL ? 'detached' : 'attached';
			$prev = [];
			foreach ($listeners as $item) {
				if (!in_array($item, $prev, TRUE)) {
					$item[0]->$method($item[1]);
					$prev[] = $item;
				}
			}
		}
	}


	/********************* cloneable, serializable ****************d*g**/


	/**
	 * Object cloning.
	 */
	public function __clone()
	{
		if ($this->parent === NULL) {
			return;

		} elseif ($this->parent instanceof Container) {
			$this->parent = $this->parent->_isCloning();
			if ($this->parent === NULL) { // not cloning
				$this->refreshMonitors(0);
			}

		} else {
			$this->parent = NULL;
			$this->refreshMonitors(0);
		}
	}


	/**
	 * Prevents serialization.
	 */
	public function __sleep()
	{
		throw new Nette\NotImplementedException('Object serialization is not supported by class ' . get_class($this));
	}


	/**
	 * Prevents unserialization.
	 */
	public function __wakeup()
	{
		throw new Nette\NotImplementedException('Object unserialization is not supported by class ' . get_class($this));
	}

}
