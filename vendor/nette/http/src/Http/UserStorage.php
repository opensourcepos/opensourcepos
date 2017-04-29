<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Http;

use Nette;
use Nette\Security\IIdentity;


/**
 * Session storage for user object.
 */
class UserStorage implements Nette\Security\IUserStorage
{
	use Nette\SmartObject;

	/** @var string */
	private $namespace = '';

	/** @var Session */
	private $sessionHandler;

	/** @var SessionSection */
	private $sessionSection;


	public function __construct(Session $sessionHandler)
	{
		$this->sessionHandler = $sessionHandler;
	}


	/**
	 * Sets the authenticated status of this user.
	 * @param  bool
	 * @return static
	 */
	public function setAuthenticated($state)
	{
		$section = $this->getSessionSection(TRUE);
		$section->authenticated = (bool) $state;

		// Session Fixation defence
		$this->sessionHandler->regenerateId();

		if ($state) {
			$section->reason = NULL;
			$section->authTime = time(); // informative value

		} else {
			$section->reason = self::MANUAL;
			$section->authTime = NULL;
		}
		return $this;
	}


	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	public function isAuthenticated()
	{
		$session = $this->getSessionSection(FALSE);
		return $session && $session->authenticated;
	}


	/**
	 * Sets the user identity.
	 * @return static
	 */
	public function setIdentity(IIdentity $identity = NULL)
	{
		$this->getSessionSection(TRUE)->identity = $identity;
		return $this;
	}


	/**
	 * Returns current user identity, if any.
	 * @return Nette\Security\IIdentity|NULL
	 */
	public function getIdentity()
	{
		$session = $this->getSessionSection(FALSE);
		return $session ? $session->identity : NULL;
	}


	/**
	 * Changes namespace; allows more users to share a session.
	 * @param  string
	 * @return static
	 */
	public function setNamespace($namespace)
	{
		if ($this->namespace !== $namespace) {
			$this->namespace = (string) $namespace;
			$this->sessionSection = NULL;
		}
		return $this;
	}


	/**
	 * Returns current namespace.
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}


	/**
	 * Enables log out after inactivity.
	 * @param  string|int|\DateTimeInterface Number of seconds or timestamp
	 * @param  int  flag IUserStorage::CLEAR_IDENTITY
	 * @return static
	 */
	public function setExpiration($time, $flags = 0)
	{
		$section = $this->getSessionSection(TRUE);
		if ($time) {
			$time = Nette\Utils\DateTime::from($time)->format('U');
			$section->expireTime = $time;
			$section->expireDelta = $time - time();

		} else {
			unset($section->expireTime, $section->expireDelta);
		}

		$section->expireIdentity = (bool) ($flags & self::CLEAR_IDENTITY);
		$section->setExpiration($time, 'foo'); // time check
		return $this;
	}


	/**
	 * Why was user logged out?
	 * @return int|NULL
	 */
	public function getLogoutReason()
	{
		$session = $this->getSessionSection(FALSE);
		return $session ? $session->reason : NULL;
	}


	/**
	 * Returns and initializes $this->sessionSection.
	 * @return SessionSection|NULL
	 */
	protected function getSessionSection($need)
	{
		if ($this->sessionSection !== NULL) {
			return $this->sessionSection;
		}

		if (!$need && !$this->sessionHandler->exists()) {
			return NULL;
		}

		$this->sessionSection = $section = $this->sessionHandler->getSection('Nette.Http.UserStorage/' . $this->namespace);

		if (!$section->identity instanceof IIdentity || !is_bool($section->authenticated)) {
			$section->remove();
		}

		if ($section->authenticated && $section->expireDelta > 0) { // check time expiration
			if ($section->expireTime < time()) {
				$section->reason = self::INACTIVITY;
				$section->authenticated = FALSE;
				if ($section->expireIdentity) {
					unset($section->identity);
				}
			}
			$section->expireTime = time() + $section->expireDelta; // sliding expiration
		}

		if (!$section->authenticated) {
			unset($section->expireTime, $section->expireDelta, $section->expireIdentity, $section->authTime);
		}

		return $this->sessionSection;
	}

}
