<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Tracy;


/**
 * Debug Bar.
 */
class Bar
{
	/** @var IBarPanel[] */
	private $panels = [];

	/** @var bool */
	private $useSession;


	/**
	 * Add custom panel.
	 * @param  IBarPanel
	 * @param  string
	 * @return static
	 */
	public function addPanel(IBarPanel $panel, $id = NULL)
	{
		if ($id === NULL) {
			$c = 0;
			do {
				$id = get_class($panel) . ($c++ ? "-$c" : '');
			} while (isset($this->panels[$id]));
		}
		$this->panels[$id] = $panel;
		return $this;
	}


	/**
	 * Returns panel with given id
	 * @param  string
	 * @return IBarPanel|NULL
	 */
	public function getPanel($id)
	{
		return isset($this->panels[$id]) ? $this->panels[$id] : NULL;
	}


	/**
	 * Renders debug bar.
	 * @return void
	 */
	public function render()
	{
		$useSession = $this->useSession && session_status() === PHP_SESSION_ACTIVE;
		$redirectQueue = &$_SESSION['_tracy']['redirect'];

		foreach (['bar', 'redirect', 'bluescreen'] as $key) {
			$queue = &$_SESSION['_tracy'][$key];
			$queue = array_slice((array) $queue, -10, NULL, TRUE);
			$queue = array_filter($queue, function ($item) {
				return isset($item['time']) && $item['time'] > time() - 60;
			});
		}

		if (!Helpers::isHtmlMode() && !Helpers::isAjax()) {
			return;

		} elseif (Helpers::isAjax()) {
			$rows[] = (object) ['type' => 'ajax', 'panels' => $this->renderPanels('-ajax')];
			$dumps = Dumper::fetchLiveData();
			$contentId = $useSession ? $_SERVER['HTTP_X_TRACY_AJAX'] . '-ajax' : NULL;

		} elseif (preg_match('#^Location:#im', implode("\n", headers_list()))) { // redirect
			Dumper::fetchLiveData();
			Dumper::$livePrefix = count($redirectQueue) . 'p';
			$redirectQueue[] = [
				'panels' => $this->renderPanels('-r' . count($redirectQueue)),
				'dumps' => Dumper::fetchLiveData(),
				'time' => time(),
			];
			return;

		} else {
			$rows[] = (object) ['type' => 'main', 'panels' => $this->renderPanels()];
			$dumps = Dumper::fetchLiveData();
			foreach (array_reverse((array) $redirectQueue) as $info) {
				$rows[] = (object) ['type' => 'redirect', 'panels' => $info['panels']];
				$dumps += $info['dumps'];
			}
			$redirectQueue = NULL;
			$contentId = $useSession ? substr(md5(uniqid('', TRUE)), 0, 10) : NULL;
		}

		ob_start(function () {});
		require __DIR__ . '/assets/Bar/panels.phtml';
		require __DIR__ . '/assets/Bar/bar.phtml';
		$content = Helpers::fixEncoding(ob_get_clean());

		if ($contentId) {
			$_SESSION['_tracy']['bar'][$contentId] = ['content' => $content, 'dumps' => $dumps, 'time' => time()];
		}

		if (Helpers::isHtmlMode()) {
			$nonce = Helpers::getNonce();
			require __DIR__ . '/assets/Bar/loader.phtml';
		}
	}


	/**
	 * @return array
	 */
	private function renderPanels($suffix = NULL)
	{
		set_error_handler(function ($severity, $message, $file, $line) {
			if (error_reporting() & $severity) {
				throw new \ErrorException($message, 0, $severity, $file, $line);
			}
		});

		$obLevel = ob_get_level();
		$panels = [];

		foreach ($this->panels as $id => $panel) {
			$idHtml = preg_replace('#[^a-z0-9]+#i', '-', $id) . $suffix;
			try {
				$tab = (string) $panel->getTab();
				$panelHtml = $tab ? (string) $panel->getPanel() : NULL;
				if ($tab && $panel instanceof \Nette\Diagnostics\IBarPanel) {
					$e = new \Exception('Support for Nette\Diagnostics\IBarPanel is deprecated');
				}

			} catch (\Throwable $e) {
			} catch (\Exception $e) {
			}
			if (isset($e)) {
				while (ob_get_level() > $obLevel) { // restore ob-level if broken
					ob_end_clean();
				}
				$idHtml = "error-$idHtml";
				$tab = "Error in $id";
				$panelHtml = "<h1>Error: $id</h1><div class='tracy-inner'>" . nl2br(Helpers::escapeHtml($e)) . '</div>';
				unset($e);
			}
			$panels[] = (object) ['id' => $idHtml, 'tab' => $tab, 'panel' => $panelHtml];
		}

		restore_error_handler();
		return $panels;
	}


	/**
	 * Renders debug bar assets.
	 * @return bool
	 */
	public function dispatchAssets()
	{
		$asset = isset($_GET['_tracy_bar']) ? $_GET['_tracy_bar'] : NULL;
		if ($asset === 'js') {
			header('Content-Type: text/javascript');
			header('Cache-Control: max-age=864000');
			header_remove('Pragma');
			header_remove('Set-Cookie');
			$this->renderAssets();
			return TRUE;
		}

		$this->useSession = session_status() === PHP_SESSION_ACTIVE;

		if ($this->useSession && Helpers::isAjax()) {
			header('X-Tracy-Ajax: 1'); // session must be already locked
		}

		if ($this->useSession && $asset && preg_match('#^content(-ajax)?.(\w+)$#', $asset, $m)) {
			$session = &$_SESSION['_tracy']['bar'][$m[2] . $m[1]];
			header('Content-Type: text/javascript');
			header('Cache-Control: max-age=60');
			header_remove('Set-Cookie');
			if (!$m[1]) {
				$this->renderAssets();
			}
			if ($session) {
				$method = $m[1] ? 'loadAjax' : 'init';
				echo "Tracy.Debug.$method(", json_encode($session['content']), ', ', json_encode($session['dumps']), ');';
				$session = NULL;
			}
			$session = &$_SESSION['_tracy']['bluescreen'][$m[2]];
			if ($session) {
				echo "Tracy.BlueScreen.loadAjax(", json_encode($session['content']), ', ', json_encode($session['dumps']), ');';
				$session = NULL;
			}
			return TRUE;
		}
	}


	private function renderAssets()
	{
		$css = array_map('file_get_contents', [
			__DIR__ . '/assets/Bar/bar.css',
			__DIR__ . '/assets/Toggle/toggle.css',
			__DIR__ . '/assets/Dumper/dumper.css',
			__DIR__ . '/assets/BlueScreen/bluescreen.css',
		]);
		$css = json_encode(preg_replace('#\s+#u', ' ', implode($css)));
		echo "(function(){var el = document.createElement('style'); el.className='tracy-debug'; el.textContent=$css; document.head.appendChild(el);})();\n";

		array_map('readfile', [
			__DIR__ . '/assets/Bar/bar.js',
			__DIR__ . '/assets/Toggle/toggle.js',
			__DIR__ . '/assets/Dumper/dumper.js',
			__DIR__ . '/assets/BlueScreen/bluescreen.js',
		]);
	}

}
