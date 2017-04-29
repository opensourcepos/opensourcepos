<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Application\UI;

use Nette;
use Nette\Application;
use Nette\Application\Responses;
use Nette\Application\Helpers;
use Nette\Http;


/**
 * Presenter component represents a webpage instance. It converts Request to IResponse.
 *
 * @property-read Nette\Application\Request $request
 * @property-read string $action
 * @property      string $view
 * @property      string $layout
 * @property-read \stdClass $payload
 * @property-read Nette\DI\Container $context
 * @property-read Nette\Http\Session $session
 * @property-read Nette\Security\User $user
 */
abstract class Presenter extends Control implements Application\IPresenter
{
	/** bad link handling {@link Presenter::$invalidLinkMode} */
	const INVALID_LINK_SILENT = 0b0000,
		INVALID_LINK_WARNING = 0b0001,
		INVALID_LINK_EXCEPTION = 0b0010,
		INVALID_LINK_TEXTUAL = 0b0100;

	/** @internal special parameter key */
	const SIGNAL_KEY = 'do',
		ACTION_KEY = 'action',
		FLASH_KEY = '_fid',
		DEFAULT_ACTION = 'default';

	/** @var int */
	public $invalidLinkMode;

	/** @var callable[]  function (Presenter $sender, IResponse $response = NULL); Occurs when the presenter is shutting down */
	public $onShutdown;

	/** @var Nette\Application\Request|NULL */
	private $request;

	/** @var Nette\Application\IResponse */
	private $response;

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = TRUE;

	/** @var bool  use absolute Urls or paths? */
	public $absoluteUrls = FALSE;

	/** @var array */
	private $globalParams;

	/** @var array */
	private $globalState;

	/** @var array */
	private $globalStateSinces;

	/** @var string */
	private $action;

	/** @var string */
	private $view;

	/** @var string */
	private $layout;

	/** @var \stdClass */
	private $payload;

	/** @var string */
	private $signalReceiver;

	/** @var string */
	private $signal;

	/** @var bool */
	private $ajaxMode;

	/** @var bool */
	private $startupCheck;

	/** @var Nette\Application\Request|NULL */
	private $lastCreatedRequest;

	/** @var array */
	private $lastCreatedRequestFlag;

	/** @var Nette\DI\Container */
	private $context;

	/** @var Nette\Http\IRequest */
	private $httpRequest;

	/** @var Nette\Http\IResponse */
	private $httpResponse;

	/** @var Nette\Http\Session */
	private $session;

	/** @var Nette\Application\IPresenterFactory */
	private $presenterFactory;

	/** @var Nette\Application\IRouter */
	private $router;

	/** @var Nette\Security\User */
	private $user;

	/** @var ITemplateFactory */
	private $templateFactory;

	/** @var Nette\Http\Url */
	private $refUrlCache;


	public function __construct()
	{
		$this->payload = new \stdClass;
	}


	/**
	 * @return Nette\Application\Request|NULL
	 */
	public function getRequest()
	{
		return $this->request;
	}


	/**
	 * Returns self.
	 * @return Presenter
	 */
	public function getPresenter($throw = TRUE)
	{
		return $this;
	}


	/**
	 * Returns a name that uniquely identifies component.
	 * @return string
	 */
	public function getUniqueId()
	{
		return '';
	}


	/********************* interface IPresenter ****************d*g**/


	/**
	 * @return Nette\Application\IResponse
	 */
	public function run(Application\Request $request)
	{
		try {
			// STARTUP
			$this->request = $request;
			$this->payload = $this->payload ?: new \stdClass;
			$this->setParent($this->getParent(), $request->getPresenterName());

			if (!$this->httpResponse->isSent()) {
				$this->httpResponse->addHeader('Vary', 'X-Requested-With');
			}

			$this->initGlobalParameters();
			$this->checkRequirements($this->getReflection());
			$this->startup();
			if (!$this->startupCheck) {
				$class = $this->getReflection()->getMethod('startup')->getDeclaringClass()->getName();
				throw new Nette\InvalidStateException("Method $class::startup() or its descendant doesn't call parent::startup().");
			}
			// calls $this->action<Action>()
			$this->tryCall($this->formatActionMethod($this->action), $this->params);

			// autoload components
			foreach ($this->globalParams as $id => $foo) {
				$this->getComponent($id, FALSE);
			}

			if ($this->autoCanonicalize) {
				$this->canonicalize();
			}
			if ($this->httpRequest->isMethod('head')) {
				$this->terminate();
			}

			// SIGNAL HANDLING
			// calls $this->handle<Signal>()
			$this->processSignal();

			// RENDERING VIEW
			$this->beforeRender();
			// calls $this->render<View>()
			$this->tryCall($this->formatRenderMethod($this->view), $this->params);
			$this->afterRender();

			// save component tree persistent state
			$this->saveGlobalState();
			if ($this->isAjax()) {
				$this->payload->state = $this->getGlobalState();
			}

			// finish template rendering
			if ($this->getTemplate()) {
				$this->sendTemplate();
			}

		} catch (Application\AbortException $e) {
			// continue with shutting down
			if ($this->isAjax()) {
				try {
					$hasPayload = (array) $this->payload;
					unset($hasPayload['state']);
					if ($this->response instanceof Responses\TextResponse && $this->isControlInvalid()) {
						$this->snippetMode = TRUE;
						$this->response->send($this->httpRequest, $this->httpResponse);
						$this->sendPayload();
					} elseif (!$this->response && $hasPayload) { // back compatibility for use terminate() instead of sendPayload()
						trigger_error('Use $presenter->sendPayload() instead of terminate() to send payload.');
						$this->sendPayload();
					}
				} catch (Application\AbortException $e) {
				}
			}

			if ($this->hasFlashSession()) {
				$this->getFlashSession()->setExpiration($this->response instanceof Responses\RedirectResponse ? '+ 30 seconds' : '+ 3 seconds');
			}

			// SHUTDOWN
			$this->onShutdown($this, $this->response);
			$this->shutdown($this->response);

			return $this->response;
		}
	}


	/**
	 * @return void
	 */
	protected function startup()
	{
		$this->startupCheck = TRUE;
	}


	/**
	 * Common render method.
	 * @return void
	 */
	protected function beforeRender()
	{
	}


	/**
	 * Common render method.
	 * @return void
	 */
	protected function afterRender()
	{
	}


	/**
	 * @param  Nette\Application\IResponse
	 * @return void
	 */
	protected function shutdown($response)
	{
	}


	/**
	 * Checks authorization.
	 * @return void
	 */
	public function checkRequirements($element)
	{
		$user = (array) ComponentReflection::parseAnnotation($element, 'User');
		if (in_array('loggedIn', $user, TRUE) && !$this->getUser()->isLoggedIn()) {
			throw new Application\ForbiddenRequestException;
		}
	}


	/********************* signal handling ****************d*g**/


	/**
	 * @return void
	 * @throws BadSignalException
	 */
	public function processSignal()
	{
		if ($this->signal === NULL) {
			return;
		}

		$component = $this->signalReceiver === '' ? $this : $this->getComponent($this->signalReceiver, FALSE);
		if ($component === NULL) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not found.");

		} elseif (!$component instanceof ISignalReceiver) {
			throw new BadSignalException("The signal receiver component '$this->signalReceiver' is not ISignalReceiver implementor.");
		}

		$component->signalReceived($this->signal);
		$this->signal = NULL;
	}


	/**
	 * Returns pair signal receiver and name.
	 * @return array|NULL
	 */
	public function getSignal()
	{
		return $this->signal === NULL ? NULL : [$this->signalReceiver, $this->signal];
	}


	/**
	 * Checks if the signal receiver is the given one.
	 * @param  mixed  component or its id
	 * @param  string signal name (optional)
	 * @return bool
	 */
	public function isSignalReceiver($component, $signal = NULL)
	{
		if ($component instanceof Nette\ComponentModel\Component) {
			$component = $component === $this ? '' : $component->lookupPath(__CLASS__, TRUE);
		}

		if ($this->signal === NULL) {
			return FALSE;

		} elseif ($signal === TRUE) {
			return $component === ''
				|| strncmp($this->signalReceiver . '-', $component . '-', strlen($component) + 1) === 0;

		} elseif ($signal === NULL) {
			return $this->signalReceiver === $component;

		} else {
			return $this->signalReceiver === $component && strcasecmp($signal, $this->signal) === 0;
		}
	}


	/********************* rendering ****************d*g**/


	/**
	 * Returns current action name.
	 * @return string
	 */
	public function getAction($fullyQualified = FALSE)
	{
		return $fullyQualified ? ':' . $this->getName() . ':' . $this->action : $this->action;
	}


	/**
	 * Changes current action. Only alphanumeric characters are allowed.
	 * @param  string
	 * @return void
	 */
	public function changeAction($action)
	{
		if (is_string($action) && Nette\Utils\Strings::match($action, '#^[a-zA-Z0-9][a-zA-Z0-9_\x7f-\xff]*\z#')) {
			$this->action = $action;
			$this->view = $action;

		} else {
			$this->error('Action name is not alphanumeric string.');
		}
	}


	/**
	 * Returns current view.
	 * @return string
	 */
	public function getView()
	{
		return $this->view;
	}


	/**
	 * Changes current view. Any name is allowed.
	 * @param  string
	 * @return static
	 */
	public function setView($view)
	{
		$this->view = (string) $view;
		return $this;
	}


	/**
	 * Returns current layout name.
	 * @return string|FALSE
	 */
	public function getLayout()
	{
		return $this->layout;
	}


	/**
	 * Changes or disables layout.
	 * @param  string|FALSE
	 * @return static
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout === FALSE ? FALSE : (string) $layout;
		return $this;
	}


	/**
	 * @return void
	 * @throws Nette\Application\BadRequestException if no template found
	 * @throws Nette\Application\AbortException
	 */
	public function sendTemplate()
	{
		$template = $this->getTemplate();
		if (!$template->getFile()) {
			$files = $this->formatTemplateFiles();
			foreach ($files as $file) {
				if (is_file($file)) {
					$template->setFile($file);
					break;
				}
			}

			if (!$template->getFile()) {
				$file = preg_replace('#^.*([/\\\\].{1,70})\z#U', "\xE2\x80\xA6\$1", reset($files));
				$file = strtr($file, '/', DIRECTORY_SEPARATOR);
				$this->error("Page not found. Missing template '$file'.");
			}
		}

		$this->sendResponse(new Responses\TextResponse($template));
	}


	/**
	 * Finds layout template file name.
	 * @return string|NULL
	 * @internal
	 */
	public function findLayoutTemplateFile()
	{
		if ($this->layout === FALSE) {
			return;
		}
		$files = $this->formatLayoutTemplateFiles();
		foreach ($files as $file) {
			if (is_file($file)) {
				return $file;
			}
		}

		if ($this->layout) {
			$file = preg_replace('#^.*([/\\\\].{1,70})\z#U', "\xE2\x80\xA6\$1", reset($files));
			$file = strtr($file, '/', DIRECTORY_SEPARATOR);
			throw new Nette\FileNotFoundException("Layout not found. Missing template '$file'.");
		}
	}


	/**
	 * Formats layout template file names.
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		if (preg_match('#/|\\\\#', $this->layout)) {
			return [$this->layout];
		}
		list($module, $presenter) = Helpers::splitName($this->getName());
		$layout = $this->layout ? $this->layout : 'layout';
		$dir = dirname($this->getReflection()->getFileName());
		$dir = is_dir("$dir/templates") ? $dir : dirname($dir);
		$list = [
			"$dir/templates/$presenter/@$layout.latte",
			"$dir/templates/$presenter.@$layout.latte",
		];
		do {
			$list[] = "$dir/templates/@$layout.latte";
			$dir = dirname($dir);
		} while ($dir && $module && (list($module) = Helpers::splitName($module)));
		return $list;
	}


	/**
	 * Formats view template file names.
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		list(, $presenter) = Helpers::splitName($this->getName());
		$dir = dirname($this->getReflection()->getFileName());
		$dir = is_dir("$dir/templates") ? $dir : dirname($dir);
		return [
			"$dir/templates/$presenter/$this->view.latte",
			"$dir/templates/$presenter.$this->view.latte",
		];
	}


	/**
	 * Formats action method name.
	 * @param  string
	 * @return string
	 */
	public static function formatActionMethod($action)
	{
		return 'action' . $action;
	}


	/**
	 * Formats render view method name.
	 * @param  string
	 * @return string
	 */
	public static function formatRenderMethod($view)
	{
		return 'render' . $view;
	}


	/**
	 * @return ITemplate
	 */
	protected function createTemplate()
	{
		return $this->getTemplateFactory()->createTemplate($this);
	}


	/********************* partial AJAX rendering ****************d*g**/


	/**
	 * @return \stdClass
	 */
	public function getPayload()
	{
		return $this->payload;
	}


	/**
	 * Is AJAX request?
	 * @return bool
	 */
	public function isAjax()
	{
		if ($this->ajaxMode === NULL) {
			$this->ajaxMode = $this->httpRequest->isAjax();
		}
		return $this->ajaxMode;
	}


	/**
	 * Sends AJAX payload to the output.
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function sendPayload()
	{
		$this->sendResponse(new Responses\JsonResponse($this->payload));
	}


	/**
	 * Sends JSON data to the output.
	 * @param  mixed
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function sendJson($data)
	{
		$this->sendResponse(new Responses\JsonResponse($data));
	}


	/********************* navigation & flow ****************d*g**/


	/**
	 * Sends response and terminates presenter.
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function sendResponse(Application\IResponse $response)
	{
		$this->response = $response;
		$this->terminate();
	}


	/**
	 * Correctly terminates presenter.
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function terminate()
	{
		throw new Application\AbortException();
	}


	/**
	 * Forward to another presenter or action.
	 * @param  string|Nette\Application\Request
	 * @param  array|mixed
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function forward($destination, $args = [])
	{
		if ($destination instanceof Application\Request) {
			$this->sendResponse(new Responses\ForwardResponse($destination));
		}

		$args = func_num_args() < 3 && is_array($args) ? $args : array_slice(func_get_args(), 1);
		$this->createRequest($this, $destination, $args, 'forward');
		$this->sendResponse(new Responses\ForwardResponse($this->lastCreatedRequest));
	}


	/**
	 * Redirect to another URL and ends presenter execution.
	 * @param  string
	 * @param  int HTTP error code
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function redirectUrl($url, $httpCode = NULL)
	{
		if ($this->isAjax()) {
			$this->payload->redirect = (string) $url;
			$this->sendPayload();

		} elseif (!$httpCode) {
			$httpCode = $this->httpRequest->isMethod('post')
				? Http\IResponse::S303_POST_GET
				: Http\IResponse::S302_FOUND;
		}
		$this->sendResponse(new Responses\RedirectResponse($url, $httpCode));
	}


	/**
	 * Throws HTTP error.
	 * @param  string
	 * @param  int HTTP error code
	 * @return void
	 * @throws Nette\Application\BadRequestException
	 */
	public function error($message = NULL, $httpCode = Http\IResponse::S404_NOT_FOUND)
	{
		throw new Application\BadRequestException($message, $httpCode);
	}


	/**
	 * Link to myself.
	 * @return string
	 * @deprecated
	 */
	public function backlink()
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		return $this->getAction(TRUE);
	}


	/**
	 * Returns the last created Request.
	 * @return Nette\Application\Request|NULL
	 * @internal
	 */
	public function getLastCreatedRequest()
	{
		return $this->lastCreatedRequest;
	}


	/**
	 * Returns the last created Request flag.
	 * @param  string
	 * @return bool
	 * @internal
	 */
	public function getLastCreatedRequestFlag($flag)
	{
		return !empty($this->lastCreatedRequestFlag[$flag]);
	}


	/**
	 * Conditional redirect to canonicalized URI.
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function canonicalize()
	{
		if (!$this->isAjax() && ($this->request->isMethod('get') || $this->request->isMethod('head'))) {
			try {
				$url = $this->createRequest($this, $this->action, $this->getGlobalState() + $this->request->getParameters(), 'redirectX');
			} catch (InvalidLinkException $e) {
			}
			if (isset($url) && !$this->httpRequest->getUrl()->isEqual($url)) {
				$this->sendResponse(new Responses\RedirectResponse($url, Http\IResponse::S301_MOVED_PERMANENTLY));
			}
		}
	}


	/**
	 * Attempts to cache the sent entity by its last modification date.
	 * @param  string|int|\DateTimeInterface  last modified time
	 * @param  string strong entity tag validator
	 * @param  mixed  optional expiration time
	 * @return void
	 * @throws Nette\Application\AbortException
	 */
	public function lastModified($lastModified, $etag = NULL, $expire = NULL)
	{
		if ($expire !== NULL) {
			$this->httpResponse->setExpiration($expire);
		}
		$helper = new Http\Context($this->httpRequest, $this->httpResponse);
		if (!$helper->isModified($lastModified, $etag)) {
			$this->terminate();
		}
	}


	/**
	 * Request/URL factory.
	 * @param  Component  base
	 * @param  string   destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array    array of arguments
	 * @param  string   forward|redirect|link
	 * @return string|NULL   URL
	 * @throws InvalidLinkException
	 * @internal
	 */
	protected function createRequest($component, $destination, array $args, $mode)
	{
		// note: createRequest supposes that saveState(), run() & tryCall() behaviour is final

		$this->lastCreatedRequest = $this->lastCreatedRequestFlag = NULL;

		// PARSE DESTINATION
		// 1) fragment
		$a = strpos($destination, '#');
		if ($a === FALSE) {
			$fragment = '';
		} else {
			$fragment = substr($destination, $a);
			$destination = substr($destination, 0, $a);
		}

		// 2) ?query syntax
		$a = strpos($destination, '?');
		if ($a !== FALSE) {
			parse_str(substr($destination, $a + 1), $args);
			$destination = substr($destination, 0, $a);
		}

		// 3) URL scheme
		$a = strpos($destination, '//');
		if ($a === FALSE) {
			$scheme = FALSE;
		} else {
			$scheme = substr($destination, 0, $a);
			$destination = substr($destination, $a + 2);
		}

		// 4) signal or empty
		if (!$component instanceof self || substr($destination, -1) === '!') {
			list($cname, $signal) = Helpers::splitName(rtrim($destination, '!'));
			if ($cname !== '') {
				$component = $component->getComponent(strtr($cname, ':', '-'));
			}
			if ($signal === '') {
				throw new InvalidLinkException('Signal must be non-empty string.');
			}
			$destination = 'this';
		}

		if ($destination == NULL) {  // intentionally ==
			throw new InvalidLinkException('Destination must be non-empty string.');
		}

		// 5) presenter: action
		$current = FALSE;
		list($presenter, $action) = Helpers::splitName($destination);
		if ($presenter === '') {
			$action = $destination === 'this' ? $this->action : $action;
			$presenter = $this->getName();
			$presenterClass = get_class($this);

		} else {
			if ($presenter[0] === ':') { // absolute
				$presenter = substr($presenter, 1);
				if (!$presenter) {
					throw new InvalidLinkException("Missing presenter name in '$destination'.");
				}
			} else { // relative
				list($module, , $sep) = Helpers::splitName($this->getName());
				$presenter = $module . $sep . $presenter;
			}
			if (!$this->presenterFactory) {
				throw new Nette\InvalidStateException('Unable to create link to other presenter, service PresenterFactory has not been set.');
			}
			try {
				$presenterClass = $this->presenterFactory->getPresenterClass($presenter);
			} catch (Application\InvalidPresenterException $e) {
				throw new InvalidLinkException($e->getMessage(), NULL, $e);
			}
		}

		// PROCESS SIGNAL ARGUMENTS
		if (isset($signal)) { // $component must be IStatePersistent
			$reflection = new ComponentReflection(get_class($component));
			if ($signal === 'this') { // means "no signal"
				$signal = '';
				if (array_key_exists(0, $args)) {
					throw new InvalidLinkException("Unable to pass parameters to 'this!' signal.");
				}

			} elseif (strpos($signal, self::NAME_SEPARATOR) === FALSE) {
				// counterpart of signalReceived() & tryCall()
				$method = $component->formatSignalMethod($signal);
				if (!$reflection->hasCallableMethod($method)) {
					throw new InvalidLinkException("Unknown signal '$signal', missing handler {$reflection->getName()}::$method()");
				}
				// convert indexed parameters to named
				self::argsToParams(get_class($component), $method, $args, [], $missing);
			}

			// counterpart of IStatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$component->saveState($args);
			}

			if ($args && $component !== $this) {
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
				foreach ($args as $key => $val) {
					unset($args[$key]);
					$args[$prefix . $key] = $val;
				}
			}
		}

		// PROCESS ARGUMENTS
		if (is_subclass_of($presenterClass, __CLASS__)) {
			if ($action === '') {
				$action = self::DEFAULT_ACTION;
			}

			$current = ($action === '*' || strcasecmp($action, $this->action) === 0) && $presenterClass === get_class($this);

			$reflection = new ComponentReflection($presenterClass);

			// counterpart of run() & tryCall()
			$method = $presenterClass::formatActionMethod($action);
			if (!$reflection->hasCallableMethod($method)) {
				$method = $presenterClass::formatRenderMethod($action);
				if (!$reflection->hasCallableMethod($method)) {
					$method = NULL;
				}
			}

			// convert indexed parameters to named
			if ($method === NULL) {
				if (array_key_exists(0, $args)) {
					throw new InvalidLinkException("Unable to pass parameters to action '$presenter:$action', missing corresponding method.");
				}
			} else {
				self::argsToParams($presenterClass, $method, $args, $destination === 'this' ? $this->params : [], $missing);
			}

			// counterpart of IStatePersistent
			if ($args && array_intersect_key($args, $reflection->getPersistentParams())) {
				$this->saveState($args, $reflection);
			}

			if ($mode === 'redirect') {
				$this->saveGlobalState();
			}

			$globalState = $this->getGlobalState($destination === 'this' ? NULL : $presenterClass);
			if ($current && $args) {
				$tmp = $globalState + $this->params;
				foreach ($args as $key => $val) {
					if (http_build_query([$val]) !== (isset($tmp[$key]) ? http_build_query([$tmp[$key]]) : '')) {
						$current = FALSE;
						break;
					}
				}
			}
			$args += $globalState;
		}

		if ($mode !== 'test' && !empty($missing)) {
			foreach ($missing as $rp) {
				if (!array_key_exists($rp->getName(), $args)) {
					throw new InvalidLinkException("Missing parameter \${$rp->getName()} required by {$rp->getDeclaringClass()->getName()}::{$rp->getDeclaringFunction()->getName()}()");
				}
			}
		}

		// ADD ACTION & SIGNAL & FLASH
		if ($action) {
			$args[self::ACTION_KEY] = $action;
		}
		if (!empty($signal)) {
			$args[self::SIGNAL_KEY] = $component->getParameterId($signal);
			$current = $current && $args[self::SIGNAL_KEY] === $this->getParameter(self::SIGNAL_KEY);
		}
		if (($mode === 'redirect' || $mode === 'forward') && $this->hasFlashSession()) {
			$args[self::FLASH_KEY] = $this->getFlashKey();
		}

		$this->lastCreatedRequest = new Application\Request(
			$presenter,
			Application\Request::FORWARD,
			$args,
			[],
			[]
		);
		$this->lastCreatedRequestFlag = ['current' => $current];

		if ($mode === 'forward' || $mode === 'test') {
			return;
		}

		// CONSTRUCT URL
		if ($this->refUrlCache === NULL) {
			$this->refUrlCache = new Http\Url($this->httpRequest->getUrl());
			$this->refUrlCache->setPath($this->httpRequest->getUrl()->getScriptPath());
		}
		if (!$this->router) {
			throw new Nette\InvalidStateException('Unable to generate URL, service Router has not been set.');
		}
		$url = $this->router->constructUrl($this->lastCreatedRequest, $this->refUrlCache);
		if ($url === NULL) {
			unset($args[self::ACTION_KEY]);
			$params = urldecode(http_build_query($args, NULL, ', '));
			throw new InvalidLinkException("No route for $presenter:$action($params)");
		}

		// make URL relative if possible
		if ($mode === 'link' && $scheme === FALSE && !$this->absoluteUrls) {
			$hostUrl = $this->refUrlCache->getHostUrl() . '/';
			if (strncmp($url, $hostUrl, strlen($hostUrl)) === 0) {
				$url = substr($url, strlen($hostUrl) - 1);
			}
		}

		return $url . $fragment;
	}


	/**
	 * Converts list of arguments to named parameters.
	 * @param  string  class name
	 * @param  string  method name
	 * @param  array   arguments
	 * @param  array   supplemental arguments
	 * @param  ReflectionParameter[]  missing arguments
	 * @return void
	 * @throws InvalidLinkException
	 * @internal
	 */
	public static function argsToParams($class, $method, &$args, $supplemental = [], &$missing = [])
	{
		$i = 0;
		$rm = new \ReflectionMethod($class, $method);
		foreach ($rm->getParameters() as $param) {
			list($type, $isClass) = ComponentReflection::getParameterType($param);
			$name = $param->getName();

			if (array_key_exists($i, $args)) {
				$args[$name] = $args[$i];
				unset($args[$i]);
				$i++;

			} elseif (array_key_exists($name, $args)) {
				// continue with process

			} elseif (array_key_exists($name, $supplemental)) {
				$args[$name] = $supplemental[$name];
			}

			if (!isset($args[$name])) {
				if (!$param->isDefaultValueAvailable() && !$param->allowsNull() && $type !== 'NULL' && $type !== 'array') {
					$missing[] = $param;
					unset($args[$name]);
				}
				continue;
			}

			if (!ComponentReflection::convertType($args[$name], $type, $isClass)) {
				throw new InvalidLinkException(sprintf(
					'Argument $%s passed to %s() must be %s, %s given.',
					$name,
					$rm->getDeclaringClass()->getName() . '::' . $rm->getName(),
					$type === 'NULL' ? 'scalar' : $type,
					is_object($args[$name]) ? get_class($args[$name]) : gettype($args[$name])
				));
			}

			$def = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL;
			if ($args[$name] === $def || ($def === NULL && $args[$name] === '')) {
				$args[$name] = NULL; // value transmit is unnecessary
			}
		}

		if (array_key_exists($i, $args)) {
			throw new InvalidLinkException("Passed more parameters than method $class::{$rm->getName()}() expects.");
		}
	}


	/**
	 * Invalid link handler. Descendant can override this method to change default behaviour.
	 * @return string
	 * @throws InvalidLinkException
	 */
	protected function handleInvalidLink(InvalidLinkException $e)
	{
		if ($this->invalidLinkMode & self::INVALID_LINK_EXCEPTION) {
			throw $e;
		} elseif ($this->invalidLinkMode & self::INVALID_LINK_WARNING) {
			trigger_error('Invalid link: ' . $e->getMessage(), E_USER_WARNING);
		}
		return $this->invalidLinkMode & self::INVALID_LINK_TEXTUAL
			? '#error: ' . $e->getMessage()
			: '#';
	}


	/********************* request serialization ****************d*g**/


	/**
	 * Stores current request to session.
	 * @param  mixed  optional expiration time
	 * @return string key
	 */
	public function storeRequest($expiration = '+ 10 minutes')
	{
		$session = $this->getSession('Nette.Application/requests');
		do {
			$key = Nette\Utils\Random::generate(5);
		} while (isset($session[$key]));

		$session[$key] = [$this->user ? $this->user->getId() : NULL, $this->request];
		$session->setExpiration($expiration, $key);
		return $key;
	}


	/**
	 * Restores request from session.
	 * @param  string key
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$session = $this->getSession('Nette.Application/requests');
		if (!isset($session[$key]) || ($session[$key][0] !== NULL && $session[$key][0] !== $this->getUser()->getId())) {
			return;
		}
		$request = clone $session[$key][1];
		unset($session[$key]);
		$request->setFlag(Application\Request::RESTORED, TRUE);
		$params = $request->getParameters();
		$params[self::FLASH_KEY] = $this->getFlashKey();
		$request->setParameters($params);
		$this->sendResponse(new Responses\ForwardResponse($request));
	}


	/********************* interface IStatePersistent ****************d*g**/


	/**
	 * Returns array of persistent components.
	 * This default implementation detects components by class-level annotation @persistent(cmp1, cmp2).
	 * @return array
	 */
	public static function getPersistentComponents()
	{
		return (array) ComponentReflection::parseAnnotation(new \ReflectionClass(get_called_class()), 'persistent');
	}


	/**
	 * Saves state information for all subcomponents to $this->globalState.
	 * @return array
	 */
	protected function getGlobalState($forClass = NULL)
	{
		$sinces = &$this->globalStateSinces;

		if ($this->globalState === NULL) {
			$state = [];
			foreach ($this->globalParams as $id => $params) {
				$prefix = $id . self::NAME_SEPARATOR;
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
				}
			}
			$this->saveState($state, $forClass ? new ComponentReflection($forClass) : NULL);

			if ($sinces === NULL) {
				$sinces = [];
				foreach ($this->getReflection()->getPersistentParams() as $name => $meta) {
					$sinces[$name] = $meta['since'];
				}
			}

			$components = $this->getReflection()->getPersistentComponents();
			$iterator = $this->getComponents(TRUE, IStatePersistent::class);

			foreach ($iterator as $name => $component) {
				if ($iterator->getDepth() === 0) {
					// counts with Nette\Application\RecursiveIteratorIterator::SELF_FIRST
					$since = isset($components[$name]['since']) ? $components[$name]['since'] : FALSE; // FALSE = nonpersistent
				}
				$prefix = $component->getUniqueId() . self::NAME_SEPARATOR;
				$params = [];
				$component->saveState($params);
				foreach ($params as $key => $val) {
					$state[$prefix . $key] = $val;
					$sinces[$prefix . $key] = $since;
				}
			}

		} else {
			$state = $this->globalState;
		}

		if ($forClass !== NULL) {
			$since = NULL;
			foreach ($state as $key => $foo) {
				if (!isset($sinces[$key])) {
					$x = strpos($key, self::NAME_SEPARATOR);
					$x = $x === FALSE ? $key : substr($key, 0, $x);
					$sinces[$key] = isset($sinces[$x]) ? $sinces[$x] : FALSE;
				}
				if ($since !== $sinces[$key]) {
					$since = $sinces[$key];
					$ok = $since && is_a($forClass, $since, TRUE);
				}
				if (!$ok) {
					unset($state[$key]);
				}
			}
		}

		return $state;
	}


	/**
	 * Permanently saves state information for all subcomponents to $this->globalState.
	 * @return void
	 */
	protected function saveGlobalState()
	{
		$this->globalParams = [];
		$this->globalState = $this->getGlobalState();
	}


	/**
	 * Initializes $this->globalParams, $this->signal & $this->signalReceiver, $this->action, $this->view. Called by run().
	 * @return void
	 * @throws Nette\Application\BadRequestException if action name is not valid
	 */
	private function initGlobalParameters()
	{
		// init $this->globalParams
		$this->globalParams = [];
		$selfParams = [];

		$params = $this->request->getParameters();
		if (($tmp = $this->request->getPost('_' . self::SIGNAL_KEY)) !== NULL) {
			$params[self::SIGNAL_KEY] = $tmp;
		} elseif ($this->isAjax()) {
			$params += $this->request->getPost();
			if (($tmp = $this->request->getPost(self::SIGNAL_KEY)) !== NULL) {
				$params[self::SIGNAL_KEY] = $tmp;
			}
		}

		foreach ($params as $key => $value) {
			if (!preg_match('#^((?:[a-z0-9_]+-)*)((?!\d+\z)[a-z0-9_]+)\z#i', $key, $matches)) {
				continue;
			} elseif (!$matches[1]) {
				$selfParams[$key] = $value;
			} else {
				$this->globalParams[substr($matches[1], 0, -1)][$matches[2]] = $value;
			}
		}

		// init & validate $this->action & $this->view
		$this->changeAction(isset($selfParams[self::ACTION_KEY]) ? $selfParams[self::ACTION_KEY] : self::DEFAULT_ACTION);

		// init $this->signalReceiver and key 'signal' in appropriate params array
		$this->signalReceiver = $this->getUniqueId();
		if (isset($selfParams[self::SIGNAL_KEY])) {
			$param = $selfParams[self::SIGNAL_KEY];
			if (!is_string($param)) {
				$this->error('Signal name is not string.');
			}
			$pos = strrpos($param, '-');
			if ($pos) {
				$this->signalReceiver = substr($param, 0, $pos);
				$this->signal = substr($param, $pos + 1);
			} else {
				$this->signalReceiver = $this->getUniqueId();
				$this->signal = $param;
			}
			if ($this->signal == NULL) { // intentionally ==
				$this->signal = NULL;
			}
		}

		$this->loadState($selfParams);
	}


	/**
	 * Pops parameters for specified component.
	 * @param  string  component id
	 * @return array
	 * @internal
	 */
	public function popGlobalParameters($id)
	{
		if (isset($this->globalParams[$id])) {
			$res = $this->globalParams[$id];
			unset($this->globalParams[$id]);
			return $res;

		} else {
			return [];
		}
	}


	/********************* flash session ****************d*g**/


	/**
	 * @return string|NULL
	 */
	private function getFlashKey()
	{
		$flashKey = $this->getParameter(self::FLASH_KEY);
		return is_string($flashKey) && $flashKey !== ''
			? $flashKey
			: NULL;
	}


	/**
	 * Checks if a flash session namespace exists.
	 * @return bool
	 */
	public function hasFlashSession()
	{
		$flashKey = $this->getFlashKey();
		return $flashKey !== NULL
			&& $this->getSession()->hasSection('Nette.Application.Flash/' . $flashKey);
	}


	/**
	 * Returns session namespace provided to pass temporary data between redirects.
	 * @return Nette\Http\SessionSection
	 */
	public function getFlashSession()
	{
		$flashKey = $this->getFlashKey();
		if ($flashKey === NULL) {
			$this->params[self::FLASH_KEY] = $flashKey = Nette\Utils\Random::generate(4);
		}
		return $this->getSession('Nette.Application.Flash/' . $flashKey);
	}


	/********************* services ****************d*g**/


	public function injectPrimary(Nette\DI\Container $context = NULL, Application\IPresenterFactory $presenterFactory = NULL, Application\IRouter $router = NULL,
		Http\IRequest $httpRequest, Http\IResponse $httpResponse, Http\Session $session = NULL, Nette\Security\User $user = NULL, ITemplateFactory $templateFactory = NULL)
	{
		if ($this->presenterFactory !== NULL) {
			throw new Nette\InvalidStateException('Method ' . __METHOD__ . ' is intended for initialization and should not be called more than once.');
		}

		$this->context = $context;
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->session = $session;
		$this->user = $user;
		$this->templateFactory = $templateFactory;
	}


	/**
	 * Gets the context.
	 * @return Nette\DI\Container
	 * @deprecated
	 */
	public function getContext()
	{
		if (!$this->context) {
			throw new Nette\InvalidStateException('Context has not been set.');
		}
		return $this->context;
	}


	/**
	 * @return Nette\Http\IRequest
	 */
	public function getHttpRequest()
	{
		return $this->httpRequest;
	}


	/**
	 * @return Nette\Http\IResponse
	 */
	public function getHttpResponse()
	{
		return $this->httpResponse;
	}


	/**
	 * @param  string
	 * @return Nette\Http\Session|Nette\Http\SessionSection
	 */
	public function getSession($namespace = NULL)
	{
		if (!$this->session) {
			throw new Nette\InvalidStateException('Service Session has not been set.');
		}
		return $namespace === NULL ? $this->session : $this->session->getSection($namespace);
	}


	/**
	 * @return Nette\Security\User
	 */
	public function getUser()
	{
		if (!$this->user) {
			throw new Nette\InvalidStateException('Service User has not been set.');
		}
		return $this->user;
	}


	/**
	 * @return ITemplateFactory
	 */
	public function getTemplateFactory()
	{
		if (!$this->templateFactory) {
			throw new Nette\InvalidStateException('Service TemplateFactory has not been set.');
		}
		return $this->templateFactory;
	}

}
