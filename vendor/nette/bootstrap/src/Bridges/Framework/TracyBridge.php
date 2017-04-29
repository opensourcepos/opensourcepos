<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\Framework;

use Nette;
use Nette\Framework;
use Tracy;
use Tracy\Helpers;
use Tracy\BlueScreen;
use Latte;


/**
 * Initializes Tracy.
 */
class TracyBridge
{
	use Nette\StaticClass;

	public static function initialize()
	{
		$blueScreen = Tracy\Debugger::getBlueScreen();

		if (class_exists(Nette\Framework::class)) {
			$version = Framework::VERSION . (Framework::REVISION ? ' (' . Framework::REVISION . ')' : '');
			Tracy\Debugger::getBar()->getPanel('Tracy:info')->data['Nette Framework'] = $version;
			$blueScreen->info[] = "Nette Framework $version";
		}

		$blueScreen->addPanel(function ($e) {
			if ($e instanceof Latte\CompileException) {
				return [
					'tab' => 'Template',
					'panel' => (preg_match('#\n|\?#', $e->sourceName)
							? ''
							: '<p>'
								. (@is_file($e->sourceName) // @ - may trigger error
									? '<b>File:</b> ' . Helpers::editorLink($e->sourceName, $e->sourceLine)
									: '<b>' . htmlspecialchars($e->sourceName . ($e->sourceLine ? ':' . $e->sourceLine : '')) . '</b>')
								. '</p>')
						. '<pre class=code><div>'
						. BlueScreen::highlightLine(htmlspecialchars($e->sourceCode, ENT_IGNORE, 'UTF-8'), $e->sourceLine)
						. '</div></pre>',
				];
			}
		});

		$blueScreen->addPanel(function ($e) {
			if ($e instanceof Nette\Neon\Exception && preg_match('#line (\d+)#', $e->getMessage(), $m)
				&& ($trace = Helpers::findTrace($e->getTrace(), 'Nette\Neon\Decoder::decode'))
			) {
				return [
					'tab' => 'NEON',
					'panel' => ($trace2 = Helpers::findTrace($e->getTrace(), 'Nette\DI\Config\Adapters\NeonAdapter::load'))
						? '<p><b>File:</b> ' . Helpers::editorLink($trace2['args'][0], $m[1]) . '</p>'
							. BlueScreen::highlightFile($trace2['args'][0], $m[1])
						: BlueScreen::highlightPhp($trace['args'][0], $m[1]),
				];
			}
		});
	}

}
