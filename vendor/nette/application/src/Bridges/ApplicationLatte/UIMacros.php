<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\ApplicationLatte;

use Nette;
use Latte;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\CompileException;
use Nette\Utils\Strings;


/**
 * Macros for Nette\Application\UI.
 *
 * - {link destination ...} control link
 * - {plink destination ...} presenter link
 * - {snippet ?} ... {/snippet ?} control snippet
 * - n:nonce
 */
class UIMacros extends Latte\Macros\MacroSet
{
	/** @var bool */
	private $extends;


	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('control', [$me, 'macroControl']);

		$me->addMacro('href', NULL, NULL, function (MacroNode $node, PhpWriter $writer) use ($me) {
			return ' ?> href="<?php ' . $me->macroLink($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('plink', [$me, 'macroLink']);
		$me->addMacro('link', [$me, 'macroLink']);
		$me->addMacro('ifCurrent', [$me, 'macroIfCurrent'], '}'); // deprecated; use n:class="$presenter->linkCurrent ? ..."
		$me->addMacro('extends', [$me, 'macroExtends']);
		$me->addMacro('layout', [$me, 'macroExtends']);
		$me->addMacro('nonce', NULL, NULL, 'echo $this->global->uiNonce ? " nonce=\"{$this->global->uiNonce}\"" : "";');
	}


	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{
		$this->extends = FALSE;
	}


	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		return [$this->extends . 'Nette\Bridges\ApplicationLatte\UIRuntime::initialize($this, $this->parentName, $this->blocks);'];
	}


	/********************* macros ****************d*g**/


	/**
	 * {control name[:method] [params]}
	 */
	public function macroControl(MacroNode $node, PhpWriter $writer)
	{
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException('Missing control name in {control}');
		}
		$name = $writer->formatWord($words[0]);
		$method = isset($words[1]) ? ucfirst($words[1]) : '';
		$method = Strings::match($method, '#^\w*\z#') ? "render$method" : "{\"render$method\"}";

		$tokens = $node->tokenizer;
		$pos = $tokens->position;
		$param = $writer->formatArray();
		$tokens->position = $pos;
		while ($tokens->nextToken()) {
			if ($tokens->isCurrent('=>') && !$tokens->depth) {
				$wrap = TRUE;
				break;
			}
		}
		if (empty($wrap)) {
			$param = substr($param, 1, -1); // removes array() or []
		}
		return "/* line $node->startLine */ "
			. ($name[0] === '$' ? "if (is_object($name)) \$_tmp = $name; else " : '')
			. '$_tmp = $this->global->uiControl->getComponent(' . $name . '); '
			. 'if ($_tmp instanceof Nette\Application\UI\IRenderable) $_tmp->redrawControl(NULL, FALSE); '
			. ($node->modifiers === ''
				? "\$_tmp->$method($param);"
				: $writer->write("ob_start(function () {}); \$_tmp->$method($param); echo %modify(ob_get_clean());")
			);
	}


	/**
	 * {link destination [,] [params]}
	 * {plink destination [,] [params]}
	 * n:href="destination [,] [params]"
	 */
	public function macroLink(MacroNode $node, PhpWriter $writer)
	{
		$node->modifiers = preg_replace('#\|safeurl\s*(?=\||\z)#i', '', $node->modifiers);
		return $writer->using($node)
			->write('echo %escape(%modify('
				. ($node->name === 'plink' ? '$this->global->uiPresenter' : '$this->global->uiControl')
				. '->link(%node.word, %node.array?)))'
			);
	}


	/**
	 * {ifCurrent destination [,] [params]}
	 */
	public function macroIfCurrent(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
		}
		return $writer->write($node->args
			? 'if ($this->global->uiPresenter->isLinkCurrent(%node.word, %node.array?)) {'
			: 'if ($this->global->uiPresenter->getLastCreatedRequestFlag("current")) {'
		);
	}


	/**
	 * {extends auto}
	 */
	public function macroExtends(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers || $node->parentNode || $node->args !== 'auto') {
			return $this->extends = FALSE;
		}
		$this->extends = $writer->write('$this->parentName = $this->global->uiPresenter->findLayoutTemplateFile();');
	}


	/** @deprecated */
	public static function renderSnippets(Nette\Application\UI\Control $control, \stdClass $local, array $params)
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		UIRuntime::renderSnippets($control, $local, $params);
	}

}
