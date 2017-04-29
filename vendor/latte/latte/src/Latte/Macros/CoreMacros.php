<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte\Macros;

use Latte;
use Latte\CompileException;
use Latte\MacroNode;
use Latte\PhpWriter;


/**
 * Basic macros for Latte.
 *
 * - {if ?} ... {elseif ?} ... {else} ... {/if}
 * - {ifset ?} ... {elseifset ?} ... {/ifset}
 * - {for ?} ... {/for}
 * - {foreach ?} ... {/foreach}
 * - {$variable} with escaping
 * - {=expression} echo with escaping
 * - {?expression} evaluate PHP statement
 * - {_expression} echo translation with escaping
 * - {attr ?} HTML element attributes
 * - {capture ?} ... {/capture} capture block to parameter
 * - {var var => value} set template parameter
 * - {default var => value} set default template parameter
 * - {dump $var}
 * - {debugbreak}
 * - {contentType ...} HTTP Content-Type header
 * - {status ...} HTTP status
 * - {l} {r} to display { }
 */
class CoreMacros extends MacroSet
{


	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);

		$me->addMacro('if', array($me, 'macroIf'), array($me, 'macroEndIf'));
		$me->addMacro('elseif', '} elseif (%node.args) {');
		$me->addMacro('else', array($me, 'macroElse'));
		$me->addMacro('ifset', 'if (isset(%node.args)) {', '}');
		$me->addMacro('elseifset', '} elseif (isset(%node.args)) {');
		$me->addMacro('ifcontent', array($me, 'macroIfContent'), array($me, 'macroEndIfContent'));

		$me->addMacro('switch', '$_l->switch[] = (%node.args); if (FALSE) {', '} array_pop($_l->switch)');
		$me->addMacro('case', '} elseif (end($_l->switch) === (%node.args)) {');

		$me->addMacro('foreach', '', array($me, 'macroEndForeach'));
		$me->addMacro('for', 'for (%node.args) {', '}');
		$me->addMacro('while', 'while (%node.args) {', '}');
		$me->addMacro('continueIf', array($me, 'macroBreakContinueIf'));
		$me->addMacro('breakIf', array($me, 'macroBreakContinueIf'));
		$me->addMacro('first', 'if ($iterator->isFirst(%node.args)) {', '}');
		$me->addMacro('last', 'if ($iterator->isLast(%node.args)) {', '}');
		$me->addMacro('sep', 'if (!$iterator->isLast(%node.args)) {', '}');

		$me->addMacro('var', array($me, 'macroVar'));
		$me->addMacro('default', array($me, 'macroVar'));
		$me->addMacro('dump', array($me, 'macroDump'));
		$me->addMacro('debugbreak', array($me, 'macroDebugbreak'));
		$me->addMacro('l', '?>{<?php');
		$me->addMacro('r', '?>}<?php');

		$me->addMacro('_', array($me, 'macroTranslate'), array($me, 'macroTranslate'));
		$me->addMacro('=', array($me, 'macroExpr'));
		$me->addMacro('?', array($me, 'macroExpr'));

		$me->addMacro('capture', array($me, 'macroCapture'), array($me, 'macroCaptureEnd'));
		$me->addMacro('include', array($me, 'macroInclude'));
		$me->addMacro('use', array($me, 'macroUse'));
		$me->addMacro('contentType', array($me, 'macroContentType'));
		$me->addMacro('status', array($me, 'macroStatus'));
		$me->addMacro('php', array($me, 'macroExpr'));

		$me->addMacro('class', NULL, NULL, array($me, 'macroClass'));
		$me->addMacro('attr', NULL, NULL, array($me, 'macroAttr'));
	}


	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		return array('list($_b, $_g, $_l) = $template->initialize('
			. var_export($this->getCompiler()->getTemplateId(), TRUE) . ', '
			. var_export($this->getCompiler()->getContentType(), TRUE)
		. ')');
	}


	/********************* macros ****************d*g**/


	/**
	 * {if ...}
	 */
	public function macroIf(MacroNode $node, PhpWriter $writer)
	{
		if ($node->data->capture = ($node->args === '')) {
			return 'ob_start()';
		}
		if ($node->prefix === $node::PREFIX_TAG) {
			return $writer->write($node->htmlNode->closing ? 'if (array_pop($_l->ifs)) {' : 'if ($_l->ifs[] = (%node.args)) {');
		}
		return $writer->write('if (%node.args) {');
	}


	/**
	 * {/if ...}
	 */
	public function macroEndIf(MacroNode $node, PhpWriter $writer)
	{
		if ($node->data->capture) {
			if ($node->args === '') {
				throw new CompileException('Missing condition in {if} macro.');
			}
			return $writer->write('if (%node.args) '
				. (isset($node->data->else) ? '{ ob_end_clean(); ob_end_flush(); }' : 'ob_end_flush();')
				. ' else '
				. (isset($node->data->else) ? '{ $_l->else = ob_get_contents(); ob_end_clean(); ob_end_clean(); echo $_l->else; }' : 'ob_end_clean();')
			);
		}
		return '}';
	}


	/**
	 * {else}
	 */
	public function macroElse(MacroNode $node, PhpWriter $writer)
	{
		$ifNode = $node->parentNode;
		if ($ifNode && $ifNode->name === 'if' && $ifNode->data->capture) {
			if (isset($ifNode->data->else)) {
				throw new CompileException('Macro {if} supports only one {else}.');
			}
			$ifNode->data->else = TRUE;
			return 'ob_start()';
		}
		return '} else {';
	}


	/**
	 * n:ifcontent
	 */
	public function macroIfContent(MacroNode $node, PhpWriter $writer)
	{
		if (!$node->prefix) {
			throw new CompileException("Unknown macro {{$node->name}}, use n:{$node->name} attribute.");
		} elseif ($node->prefix !== MacroNode::PREFIX_NONE) {
			throw new CompileException("Unknown attribute n:{$node->prefix}-{$node->name}, use n:{$node->name} attribute.");
		}

		return $writer->write('ob_start()');
	}


	/**
	 * n:ifcontent
	 */
	public function macroEndIfContent(MacroNode $node, PhpWriter $writer)
	{
		preg_match('#(^.*?>)(.*)(<.*\z)#s', $node->content, $parts);
		$node->content = $parts[1]
			. '<?php ob_start() ?>'
			. $parts[2]
			. '<?php $_l->ifcontent = ob_get_contents(); ob_end_flush() ?>'
			. $parts[3];
		return 'rtrim($_l->ifcontent) === "" ? ob_end_clean() : ob_end_flush()';
	}


	/**
	 * {_$var |modifiers}
	 */
	public function macroTranslate(MacroNode $node, PhpWriter $writer)
	{
		if ($node->closing) {
			return $writer->write('echo %modify($template->translate(ob_get_clean()))');

		} elseif ($node->isEmpty = ($node->args !== '')) {
			return $writer->write('echo %modify($template->translate(%node.args))');

		} else {
			return 'ob_start()';
		}
	}


	/**
	 * {include "file" [,] [params]}
	 */
	public function macroInclude(MacroNode $node, PhpWriter $writer)
	{
		$code = $writer->write('$_b->templates[%var]->renderChildTemplate(%node.word, %node.array? + $template->getParameters())',
			$this->getCompiler()->getTemplateId());

		if ($node->modifiers) {
			return $writer->write('ob_start(); %raw; echo %modify(ob_get_clean())', $code);
		} else {
			return $code;
		}
	}


	/**
	 * {use class MacroSet}
	 */
	public function macroUse(MacroNode $node, PhpWriter $writer)
	{
		call_user_func(Latte\Helpers::checkCallback(array($node->tokenizer->fetchWord(), 'install')), $this->getCompiler())
			->initialize();
	}


	/**
	 * {capture $variable}
	 */
	public function macroCapture(MacroNode $node, PhpWriter $writer)
	{
		$variable = $node->tokenizer->fetchWord();
		if (substr($variable, 0, 1) !== '$') {
			throw new CompileException("Invalid capture block variable '$variable'");
		}
		$node->data->variable = $variable;
		return 'ob_start()';
	}


	/**
	 * {/capture}
	 */
	public function macroCaptureEnd(MacroNode $node, PhpWriter $writer)
	{
		return $node->data->variable . $writer->write(' = %modify(ob_get_clean())');
	}


	/**
	 * {foreach ...}
	 */
	public function macroEndForeach(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers !== '|noiterator' && preg_match('#\W(\$iterator|include|require|get_defined_vars)\W#', $this->getCompiler()->expandTokens($node->content))) {
			$node->openingCode = '<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Latte\Runtime\CachingIterator('
			. preg_replace('#(.*)\s+as\s+#i', '$1) as ', $writer->formatArgs(), 1) . ') { ?>';
			$node->closingCode = '<?php $iterations++; } array_pop($_l->its); $iterator = end($_l->its) ?>';
		} else {
			$node->openingCode = '<?php $iterations = 0; foreach (' . $writer->formatArgs() . ') { ?>';
			$node->closingCode = '<?php $iterations++; } ?>';
		}
	}


	/**
	 * {breakIf ...}
	 * {continueIf ...}
	 */
	public function macroBreakContinueIf(MacroNode $node, PhpWriter $writer)
	{
		$cmd = str_replace('If', '', $node->name);
		if ($node->parentNode && $node->parentNode->prefix === $node::PREFIX_NONE) {
			return $writer->write("if (%node.args) { echo \"</{$node->parentNode->htmlNode->name}>\\n\"; $cmd; }");
		}
		return $writer->write("if (%node.args) $cmd");
	}


	/**
	 * n:class="..."
	 */
	public function macroClass(MacroNode $node, PhpWriter $writer)
	{
		if (isset($node->htmlNode->attrs['class'])) {
			throw new CompileException('It is not possible to combine class with n:class.');
		}
		return $writer->write('if ($_l->tmp = array_filter(%node.array)) echo \' class="\', %escape(implode(" ", array_unique($_l->tmp))), \'"\'');
	}


	/**
	 * n:attr="..."
	 */
	public function macroAttr(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo Latte\Runtime\Filters::htmlAttributes(%node.array)');
	}


	/**
	 * {dump ...}
	 */
	public function macroDump(MacroNode $node, PhpWriter $writer)
	{
		$args = $writer->formatArgs();
		return $writer->write(
			'Tracy\Debugger::barDump(' . ($args ? "($args)" : 'get_defined_vars()'). ', %var)',
			$args ?: 'variables'
		);
	}


	/**
	 * {debugbreak ...}
	 */
	public function macroDebugbreak(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write(($node->args == NULL ? '' : 'if (!(%node.args)); else')
			. 'if (function_exists("debugbreak")) debugbreak(); elseif (function_exists("xdebug_break")) xdebug_break()');
	}


	/**
	 * {var ...}
	 * {default ...}
	 */
	public function macroVar(MacroNode $node, PhpWriter $writer)
	{
		if ($node->args === '' && $node->parentNode && $node->parentNode->name === 'switch') {
			return '} else {';
		}

		$var = TRUE;
		$tokens = $writer->preprocess();
		$res = new Latte\MacroTokens;
		while ($tokens->nextToken()) {
			if ($var && $tokens->isCurrent(Latte\MacroTokens::T_SYMBOL, Latte\MacroTokens::T_VARIABLE)) {
				if ($node->name === 'default') {
					$res->append("'" . ltrim($tokens->currentValue(), '$') . "'");
				} else {
					$res->append('$' . ltrim($tokens->currentValue(), '$'));
				}
				$var = NULL;

			} elseif ($tokens->isCurrent('=', '=>') && $tokens->depth === 0) {
				$res->append($node->name === 'default' ? '=>' : '=');
				$var = FALSE;

			} elseif ($tokens->isCurrent(',') && $tokens->depth === 0) {
				if ($var === NULL) {
					$res->append($node->name === 'default' ? '=>NULL' : '=NULL');
				}
				$res->append($node->name === 'default' ? ',' : ';');
				$var = TRUE;

			} elseif ($var === NULL && $node->name === 'default' && !$tokens->isCurrent(Latte\MacroTokens::T_WHITESPACE)) {
				throw new CompileException("Unexpected '{$tokens->currentValue()}' in {default $node->args}");

			} else {
				$res->append($tokens->currentToken());
			}
		}
		if ($var === NULL) {
			$res->append($node->name === 'default' ? '=>NULL' : '=NULL');
		}
		$out = $writer->quoteFilter($res)->joinAll();
		return $node->name === 'default' ? "extract(array($out), EXTR_SKIP)" : $out;
	}


	/**
	 * {= ...}
	 * {? ...}
	 */
	public function macroExpr(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write(($node->name === '=' ? 'echo ' : '') . '%modify(%node.args)');
	}


	/**
	 * {contentType ...}
	 */
	public function macroContentType(MacroNode $node, PhpWriter $writer)
	{
		if (strpos($node->args, 'xhtml') !== FALSE) {
			$this->getCompiler()->setContentType(Latte\Compiler::CONTENT_XHTML);

		} elseif (strpos($node->args, 'html') !== FALSE) {
			$this->getCompiler()->setContentType(Latte\Compiler::CONTENT_HTML);

		} elseif (strpos($node->args, 'xml') !== FALSE) {
			$this->getCompiler()->setContentType(Latte\Compiler::CONTENT_XML);

		} elseif (strpos($node->args, 'javascript') !== FALSE) {
			$this->getCompiler()->setContentType(Latte\Compiler::CONTENT_JS);

		} elseif (strpos($node->args, 'css') !== FALSE) {
			$this->getCompiler()->setContentType(Latte\Compiler::CONTENT_CSS);

		} elseif (strpos($node->args, 'calendar') !== FALSE) {
			$this->getCompiler()->setContentType(Latte\Compiler::CONTENT_ICAL);

		} else {
			$this->getCompiler()->setContentType(Latte\Compiler::CONTENT_TEXT);
		}

		// temporary solution
		if (strpos($node->args, '/')) {
			return $writer->write('header(%var)', "Content-Type: $node->args");
		}
	}


	/**
	 * {status ...}
	 */
	public function macroStatus(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write((substr($node->args, -1) === '?' ? 'if (!headers_sent()) ' : '') .
			'header((isset($_SERVER["SERVER_PROTOCOL"]) ? $_SERVER["SERVER_PROTOCOL"] : "HTTP/1.1") . " " . %0.var, TRUE, %0.var)', (int) $node->args
		);
	}

}
