<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Events\Diagnostics;

use Doctrine\Common\EventArgs;
use Kdyby;
use Kdyby\Events\Event;
use Kdyby\Events\EventManager;
use Nette;
use Nette\Utils\Callback;
use Tracy\Bar;
use Tracy\Debugger;
use Nette\Utils\Arrays;
use Tracy\Dumper;
use Tracy;


/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Panel extends Nette\Object implements Tracy\IBarPanel
{

	/**
	 * @var EventManager
	 */
	private $evm;

	/**
	 * @var Nette\DI\Container
	 */
	private $sl;

	/**
	 * @var array
	 */
	private $events = array();

	/**
	 * @var array
	 */
	private $dispatchLog = array();

	/**
	 * @var array
	 */
	private $dispatchTree = array();

	/**
	 * @var array|NULL
	 */
	private $dispatchTreePointer = NULL;

	/**
	 * @var array
	 */
	private $listenerIds = array();

	/**
	 * @var array
	 */
	private $inlineCallbacks = array();

	/**
	 * @var array
	 */
	private $registeredClasses;

	/**
	 * @var bool
	 */
	public $renderPanel = TRUE;



	public function __construct(Nette\DI\Container $sl)
	{
		$this->sl = $sl;
	}



	/**
	 * @param EventManager $evm
	 */
	public function setEventManager(EventManager $evm)
	{
		$this->evm = $evm;
		$evm->setPanel($this);
	}



	public function setServiceIds(array $listenerIds)
	{
		if (!$this->renderPanel || (is_array($this->renderPanel) && !$this->renderPanel['listeners'])) {
			return;
		}
		$this->listenerIds = $listenerIds;
	}



	public function registerEvent(Event $event)
	{
		$this->events[] = $event;
		$event->setPanel($this);
	}



	public function eventDispatch($eventName, EventArgs $args = NULL)
	{
		if (!$this->renderPanel) {
			return;
		}

		if (!is_array($this->renderPanel) || $this->renderPanel['dispatchLog']) {
			$this->dispatchLog[$eventName][] = $args;
		}

		if (!is_array($this->renderPanel) || $this->renderPanel['dispatchTree']) {
			// [parent-ref, name, args, children]
			$meta = array(&$this->dispatchTreePointer, $eventName, $args, array());
			if ($this->dispatchTreePointer === NULL) {
				$this->dispatchTree[] = & $meta;
			} else {
				$this->dispatchTreePointer[3][] = & $meta;
			}
			$this->dispatchTreePointer = & $meta;
		}
	}



	public function eventDispatched($eventName, EventArgs $args = NULL)
	{
		if (!$this->renderPanel || (is_array($this->renderPanel) && !$this->renderPanel['dispatchTree'])) {
			return;
		}
		$this->dispatchTreePointer = &$this->dispatchTreePointer[0];
	}



	public function inlineCallbacks($eventName, $inlineCallbacks)
	{
		if (!$this->renderPanel) {
			return;
		}
		$this->inlineCallbacks[$eventName] = (array) $inlineCallbacks;
	}



	/**
	 * Renders HTML code for custom tab.
	 *
	 * @return string
	 */
	public function getTab()
	{
		if (empty($this->events)) {
			return NULL;
		}

		return '<span title="Kdyby/Events">'
		. '<img width="16" height="16" src="data:image/png;base64,' . base64_encode(file_get_contents(__DIR__ . '/icon.png')) . '" />'
		. count(Arrays::flatten($this->dispatchLog)) .  ' calls'
		. '</span>';
	}



	/**
	 * Renders HTML code for custom panel.
	 *
	 * @return string
	 */
	public function getPanel()
	{
		if (!$this->renderPanel) {
			return '';
		}

		if (empty($this->events)) {
			return NULL;
		}

		$visited = array();

		$h = 'htmlspecialchars';

		$s = '';
		$s .= $this->renderPanelDispatchLog($visited);
		$s .= $this->renderPanelEvents($visited);
		$s .= $this->renderPanelListeners($visited);

		if ($s) {
			$s .= '<tr class="blank"><td colspan=2>&nbsp;</td></tr>';
		}

		$s .= $this->renderPanelDispatchTree();

		$totalEvents = count($this->listenerIds);
		$totalListeners = count(array_unique(Arrays::flatten($this->listenerIds)));

		return '<style>' . $this->renderStyles() . '</style>'.
			'<h1>' . $h($totalEvents) . ' registered events, ' . $h($totalListeners) . ' registered listeners</h1>' .
			'<div class="nette-inner tracy-inner nette-KdybyEventsPanel"><table>' . $s . '</table></div>';
	}



	private function renderPanelDispatchLog(&$visited)
	{
		if (!$this->renderPanel || (is_array($this->renderPanel) && !$this->renderPanel['dispatchLog'])) {
			return '';
		}

		$h = 'htmlspecialchars';
		$s = '';

		foreach ($this->dispatchLog as $eventName => $calls) {
			$s .= '<tr><th colspan=2 id="' . $this->formatEventId($eventName) . '">' . count($calls) . 'x ' . $h($eventName) . '</th></tr>';
			$visited[] = $eventName;

			$s .= $this->renderListeners($this->getInlineCallbacks($eventName));

			if (empty($this->listenerIds[$eventName])) {
				$s .= '<tr><td>&nbsp;</td><td>no system listeners</th></tr>';

			} else {
				$s .= $this->renderListeners($this->listenerIds[$eventName]);
			}

			$s .= $this->renderCalls($calls);
		}

		return $s;
	}



	private function renderPanelEvents(&$visited)
	{
		if (!$this->renderPanel || (is_array($this->renderPanel) && !$this->renderPanel['events'])) {
			return '';
		}

		$h = 'htmlspecialchars';
		$s = '';
		foreach ($this->events as $event) {
			/** @var Event $event */
			if (in_array($event->getName(), $visited, TRUE)) {
				continue;
			}

			$calls = $this->getEventCalls($event->getName());
			$s .= '<tr class="blank"><td colspan=2>&nbsp;</td></tr>';
			$s .= '<tr><th colspan=2>' . count($calls) . 'x ' . $h($event->getName()) . '</th></tr>';
			$visited[] = $event->getName();

			$s .= $this->renderListeners($this->getInlineCallbacks($event->getName()));

			if (empty($this->listenerIds[$event->getName()])) {
				$s .= '<tr><td>&nbsp;</td><td>no system listeners</th></tr>';

			} else {
				$s .= $this->renderListeners($this->listenerIds[$event->getName()]);
			}

			$s .= $this->renderCalls($calls);
		}

		return $s;
	}



	private function renderPanelListeners(&$visited)
	{
		if (!$this->renderPanel || (is_array($this->renderPanel) && !$this->renderPanel['listeners'])) {
			return '';
		}

		$h = 'htmlspecialchars';
		$s = '';
		foreach ($this->listenerIds as $eventName => $ids) {
			if (in_array($eventName, $visited, TRUE)) {
				continue;
			}

			$calls = $this->getEventCalls($eventName);
			$s .= '<tr class="blank"><td colspan=2>&nbsp;</td></tr>';
			$s .= '<tr><th colspan=2>' . count($calls) . 'x ' . $h($eventName) . '</th></tr>';

			$s .= $this->renderListeners($this->getInlineCallbacks($eventName));

			if (empty($ids)) {
				$s .= '<tr><td>&nbsp;</td><td>no system listeners</th></tr>';

			} else {
				$s .= $this->renderListeners($ids);
			}

			$s .= $this->renderCalls($calls);
		}

		return $s;
	}



	private function renderPanelDispatchTree()
	{
		if (!$this->renderPanel || (is_array($this->renderPanel) && !$this->renderPanel['dispatchTree'])) {
			return '';
		}

		$s = '<tr><th colspan=2>Summary event call graph</th></tr>';
		foreach ($this->dispatchTree as $item) {
			$s .= '<tr><td colspan=2>';
			$s .= $this->renderTreeItem($item);
			$s .= '</td></tr>';
		}

		return $s;
	}



	/**
	 * Renders an item in call graph.
	 *
	 * @param array $item
	 * @return string
	 */
	private function renderTreeItem(array $item)
	{
		$h = 'htmlspecialchars';

		$s = '<ul><li>';
		$s .= '<a href="#' . $this->formatEventId($item[1]) . '">' . $h($item[1]) . '</a>';
		if ($item[2]) $s .= ' (<a href="#' . $this->formatArgsId($item[2]) . '">' . get_class($item[2]) . '</a>)';

		if ($item[3]) {
			foreach ($item[3] as $child) {
				$s .= $this->renderTreeItem($child);
			}
		}

		return $s . '</li></ul>';
	}



	private function getEventCalls($eventName)
	{
		return !empty($this->dispatchLog[$eventName]) ? $this->dispatchLog[$eventName] : array();
	}



	private function getInlineCallbacks($eventName)
	{
		return !empty($this->inlineCallbacks[$eventName]) ? $this->inlineCallbacks[$eventName] : array();
	}



	private function renderListeners($ids)
	{
		static $addIcon;
		if (empty($addIcon)) {
			$addIcon = '<img width="18" height="18" src="data:image/png;base64,' . base64_encode(file_get_contents(__DIR__ . '/add.png')) . '" title="Listener" />';
		}

		$registeredClasses = $this->getClassMap();

		$h = 'htmlspecialchars';

		$shortFilename = function (\ReflectionFunctionAbstract $refl) {
			$title = '.../' . basename($refl->getFileName()) . ':' . $refl->getStartLine();

			if ($editor = Tracy\Helpers::editorUri($refl->getFileName(), $refl->getStartLine())) {
				return sprintf(' defined at <a href="%s">%s</a>', htmlspecialchars($editor), $title);
			}

			return ' defined at ' . $title;
		};

		$s = '';
		foreach ($ids as $id) {
			if (is_callable($id)) {
				$s .= '<tr><td width=18>' . $addIcon . '</td><td><pre class="nette-dump"><span class="nette-dump-object">' .
					Callback::toString($id) . ($id instanceof \Closure ? $shortFilename(Callback::toReflection($id)) : '') .
					'</span></span></th></tr>';

				continue;
			}

			if (!$this->sl->isCreated($id) && ($class = array_search($id, $registeredClasses, TRUE))) {
				$s .= '<tr><td width=18>' . $addIcon . '</td><td><pre class="nette-dump"><span class="nette-dump-object">' .
					$h(Nette\Reflection\ClassType::from($class)->getName()) .
					'</span></span></th></tr>';

			} else {
				try {
					$s .= '<tr><td width=18>' . $addIcon . '</td><td>' . self::dumpToHtml($this->sl->getService($id)) . '</th></tr>';

				} catch (\Exception $e) {
					$s .= "<tr><td colspan=2>Service $id cannot be loaded because of exception<br><br>\n" . (string) $e . '</td></th>';
				}
			}
		}

		return $s;
	}



	private static function dumpToHtml($structure)
	{
		return Dumper::toHtml($structure, array(Dumper::COLLAPSE => TRUE, Dumper::DEPTH => 2));
	}



	private function getClassMap()
	{
		if ($this->registeredClasses !== NULL) {
			return $this->registeredClasses;
		}

		if (property_exists('Nette\DI\Container', 'classes')) {
			return $this->registeredClasses = $this->sl->classes;
		}

		$refl = new Nette\Reflection\Property('Nette\DI\Container', 'meta');
		$refl->setAccessible(TRUE);
		$meta = $refl->getValue($this->sl);

		$this->registeredClasses = array();
		foreach ($meta['types'] as $type => $serviceIds) {
			if (isset($this->registeredClasses[$type])) {
				$this->registeredClasses[$type] = FALSE;
				continue;
			}

			$this->registeredClasses[$type] = $serviceIds;
		}

		return $this->registeredClasses;
	}



	private function renderCalls(array $calls)
	{
		static $runIcon;
		if (empty($runIcon)) {
			$runIcon = '<img width="18" height="18" src="data:image/png;base64,' . base64_encode(file_get_contents(__DIR__ . '/run.png')) . '" title="Event dispatch" />';
		}

		$s = '';
		foreach ($calls as $args) {
			$s .= '<tr><td width=18>' . $runIcon . '</td>';
			$s .='<td' . ($args ? ' id="' . $this->formatArgsId($args) . '">' . self::dumpToHtml($args) : '>dispatched without arguments');
			$s .= '</td></tr>';
		}

		return $s;
	}



	/**
	 * @param string
	 * @return string
	 */
	private function formatEventId($name)
	{
		return 'kdyby-event-' . md5($name);
	}



	/**
	 * @param object
	 * @return string
	 */
	private function formatArgsId($args)
	{
		return 'kdyby-event-arg-' . md5(spl_object_hash($args));
	}



	/**
	 * @return string
	 */
	protected function renderStyles()
	{
		return <<<CSS
			#nette-debug .nette-panel .nette-KdybyEventsPanel,
			#tracy-debug .tracy-panel .nette-KdybyEventsPanel { width: 670px !important;  }
			#nette-debug .nette-panel .nette-KdybyEventsPanel table,
			#tracy-debug .tracy-panel .nette-KdybyEventsPanel table { width: 655px !important; }
			#nette-debug .nette-panel .nette-KdybyEventsPanel table th,
			#tracy-debug .tracy-panel .nette-KdybyEventsPanel table th { font-size: 16px; }
			#nette-debug .nette-panel .nette-KdybyEventsPanel table tr td:first-child,
			#tracy-debug .tracy-panel .nette-KdybyEventsPanel table tr td:first-child { padding-bottom: 0; }
			#nette-debug .nette-panel .nette-KdybyEventsPanel table tr.blank td,
			#tracy-debug .tracy-panel .nette-KdybyEventsPanel table tr.blank td { background: white; height:25px; border-left:0; border-right:0; }
			#nette-debug .nette-panel .nette-KdybyEventsPanel table tr td ul,
			#tracy-debug .tracy-panel .nette-KdybyEventsPanel table tr td ul { background: url(data:image/gif;base64,R0lGODlhCQAJAIABAIODg////yH5BAEAAAEALAAAAAAJAAkAAAIPjI8GebDsHopSOVgb26EAADs=) 0 5px no-repeat; padding-left: 12px; list-style-type: none; }
CSS;
	}



	/**
	 * @param EventManager $eventManager
	 * @param \Nette\DI\Container $sl
	 * @return Panel
	 */
	public static function register(EventManager $eventManager, Nette\DI\Container $sl)
	{
		$panel = new static($sl);
		/** @var Panel $panel */

		$panel->setEventManager($eventManager);
		static::getDebuggerBar()->addPanel($panel);

		return $panel;
	}



	/**
	 * @return Bar
	 */
	private static function getDebuggerBar()
	{
		return method_exists('Tracy\Debugger', 'getBar') ? Debugger::getBar() : Debugger::$bar;
	}

}
