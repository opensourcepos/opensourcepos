<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Tracy;


/**
 * Red BlueScreen.
 */
class BlueScreen
{
	/** @var string[] */
	public $info = [];

	/** @var callable[] */
	private $panels = [];

	/** @var string[] paths to be collapsed in stack trace (e.g. core libraries) */
	public $collapsePaths = [];

	/** @var int  */
	public $maxDepth = 3;

	/** @var int  */
	public $maxLength = 150;


	public function __construct()
	{
		$this->collapsePaths[] = preg_match('#(.+/vendor)/tracy/tracy/src/Tracy$#', strtr(__DIR__, '\\', '/'), $m)
			? $m[1]
			: __DIR__;
	}


	/**
	 * Add custom panel.
	 * @param  callable
	 * @return static
	 */
	public function addPanel($panel)
	{
		if (!in_array($panel, $this->panels, TRUE)) {
			$this->panels[] = $panel;
		}
		return $this;
	}


	/**
	 * Renders blue screen.
	 * @param  \Exception|\Throwable
	 * @return void
	 */
	public function render($exception)
	{
		if (Helpers::isAjax() && session_status() === PHP_SESSION_ACTIVE) {
			ob_start(function () {});
			$this->renderTemplate($exception, __DIR__ . '/assets/BlueScreen/content.phtml');
			$contentId = $_SERVER['HTTP_X_TRACY_AJAX'];
			$_SESSION['_tracy']['bluescreen'][$contentId] = ['content' => ob_get_clean(), 'dumps' => Dumper::fetchLiveData(), 'time' => time()];

		} else {
			$this->renderTemplate($exception, __DIR__ . '/assets/BlueScreen/page.phtml');
		}
	}


	/**
	 * Renders blue screen to file (if file exists, it will not be overwritten).
	 * @param  \Exception|\Throwable
	 * @param  string file path
	 * @return void
	 */
	public function renderToFile($exception, $file)
	{
		if ($handle = @fopen($file, 'x')) {
			ob_start(); // double buffer prevents sending HTTP headers in some PHP
			ob_start(function ($buffer) use ($handle) { fwrite($handle, $buffer); }, 4096);
			$this->renderTemplate($exception, __DIR__ . '/assets/BlueScreen/page.phtml');
			ob_end_flush();
			ob_end_clean();
			fclose($handle);
		}
	}


	private function renderTemplate($exception, $template)
	{
		$info = array_filter($this->info);
		$source = Helpers::getSource();
		$sourceIsUrl = preg_match('#^https?://#', $source);
		$title = $exception instanceof \ErrorException
			? Helpers::errorTypeToString($exception->getSeverity())
			: Helpers::getClass($exception);
		$skipError = $sourceIsUrl && $exception instanceof \ErrorException && !empty($exception->skippable)
			? $source . (strpos($source, '?') ? '&' : '?') . '_tracy_skip_error'
			: NULL;
		$lastError = $exception instanceof \ErrorException || $exception instanceof \Error ? NULL : error_get_last();
		$dump = function($v) {
			return Dumper::toHtml($v, [
				Dumper::DEPTH => $this->maxDepth,
				Dumper::TRUNCATE => $this->maxLength,
				Dumper::LIVE => TRUE,
				Dumper::LOCATION => Dumper::LOCATION_CLASS,
			]);
		};
		$nonce = Helpers::getNonce();

		require $template;
	}


	/**
	 * @return \stdClass[]
	 */
	private function renderPanels($ex)
	{
		$obLevel = ob_get_level();
		$res = [];
		foreach ($this->panels as $callback) {
			try {
				$panel = call_user_func($callback, $ex);
				if (empty($panel['tab']) || empty($panel['panel'])) {
					continue;
				}
				$res[] = (object) $panel;
				continue;
			} catch (\Throwable $e) {
			} catch (\Exception $e) {
			}
			while (ob_get_level() > $obLevel) { // restore ob-level if broken
				ob_end_clean();
			}
			is_callable($callback, TRUE, $name);
			$res[] = (object) [
				'tab' => "Error in panel $name",
				'panel' => nl2br(Helpers::escapeHtml($e)),
			];
		}
		return $res;
	}


	/**
	 * Returns syntax highlighted source code.
	 * @param  string
	 * @param  int
	 * @param  int
	 * @return string|NULL
	 */
	public static function highlightFile($file, $line, $lines = 15, array $vars = NULL)
	{
		$source = @file_get_contents($file); // @ file may not exist
		if ($source) {
			$source = static::highlightPhp($source, $line, $lines, $vars);
			if ($editor = Helpers::editorUri($file, $line)) {
				$source = substr_replace($source, ' data-tracy-href="' . Helpers::escapeHtml($editor) . '"', 4, 0);
			}
			return $source;
		}
	}


	/**
	 * Returns syntax highlighted source code.
	 * @param  string
	 * @param  int
	 * @param  int
	 * @return string
	 */
	public static function highlightPhp($source, $line, $lines = 15, array $vars = NULL)
	{
		if (function_exists('ini_set')) {
			ini_set('highlight.comment', '#998; font-style: italic');
			ini_set('highlight.default', '#000');
			ini_set('highlight.html', '#06B');
			ini_set('highlight.keyword', '#D24; font-weight: bold');
			ini_set('highlight.string', '#080');
		}

		$source = str_replace(["\r\n", "\r"], "\n", $source);
		$source = explode("\n", highlight_string($source, TRUE));
		$out = $source[0]; // <code><span color=highlight.html>
		$source = str_replace('<br />', "\n", $source[1]);
		$out .= static::highlightLine($source, $line, $lines);

		if ($vars) {
			$out = preg_replace_callback('#">\$(\w+)(&nbsp;)?</span>#', function ($m) use ($vars) {
				return array_key_exists($m[1], $vars)
					? '" title="'
						. str_replace('"', '&quot;', trim(strip_tags(Dumper::toHtml($vars[$m[1]], [Dumper::DEPTH => 1]))))
						. $m[0]
					: $m[0];
			}, $out);
		}

		$out = str_replace('&nbsp;', ' ', $out);
		return "<pre class='code'><div>$out</div></pre>";
	}



	/**
	 * Returns highlighted line in HTML code.
	 * @return string
	 */
	public static function highlightLine($html, $line, $lines = 15)
	{
		$source = explode("\n", "\n" . str_replace("\r\n", "\n", $html));
		$out = '';
		$spans = 1;
		$start = $i = max(1, min($line, count($source) - 1) - (int) floor($lines * 2 / 3));
		while (--$i >= 1) { // find last highlighted block
			if (preg_match('#.*(</?span[^>]*>)#', $source[$i], $m)) {
				if ($m[1] !== '</span>') {
					$spans++;
					$out .= $m[1];
				}
				break;
			}
		}

		$source = array_slice($source, $start, $lines, TRUE);
		end($source);
		$numWidth = strlen((string) key($source));

		foreach ($source as $n => $s) {
			$spans += substr_count($s, '<span') - substr_count($s, '</span');
			$s = str_replace(["\r", "\n"], ['', ''], $s);
			preg_match_all('#<[^>]+>#', $s, $tags);
			if ($n == $line) {
				$out .= sprintf(
					"<span class='highlight'>%{$numWidth}s:    %s\n</span>%s",
					$n,
					strip_tags($s),
					implode('', $tags[0])
				);
			} else {
				$out .= sprintf("<span class='line'>%{$numWidth}s:</span>    %s\n", $n, $s);
			}
		}
		$out .= str_repeat('</span>', $spans) . '</code>';
		return $out;
	}


	/**
	 * Should a file be collapsed in stack trace?
	 * @param  string
	 * @return bool
	 */
	public function isCollapsed($file)
	{
		$file = strtr($file, '\\', '/') . '/';
		foreach ($this->collapsePaths as $path) {
			$path = strtr($path, '\\', '/') . '/';
			if (strncmp($file, $path, strlen($path)) === 0) {
				return TRUE;
			}
		}
		return FALSE;
	}

}
