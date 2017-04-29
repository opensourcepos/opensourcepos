<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * PHP code generator helpers.
 */
class PhpWriter extends Object
{
	/** @var MacroTokens */
	private $tokens;

	/** @var string */
	private $modifiers;

	/** @var Compiler */
	private $compiler;


	public static function using(MacroNode $node, Compiler $compiler = NULL)
	{
		return new static($node->tokenizer, $node->modifiers, $compiler);
	}


	public function __construct(MacroTokens $tokens, $modifiers = NULL, Compiler $compiler = NULL)
	{
		$this->tokens = $tokens;
		$this->modifiers = $modifiers;
		$this->compiler = $compiler;
	}


	/**
	 * Expands %node.word, %node.array, %node.args, %escape(), %modify(), %var, %raw, %word in code.
	 * @param  string
	 * @return string
	 */
	public function write($mask)
	{
		$mask = preg_replace('#%(node|\d+)\.#', '%$1_', $mask);
		$me = $this;
		$mask = preg_replace_callback('#%escape(\(([^()]*+|(?1))+\))#', function ($m) use ($me) {
			return $me->escapeFilter(new MacroTokens(substr($m[1], 1, -1)))->joinAll();
		}, $mask);
		$mask = preg_replace_callback('#%modify(\(([^()]*+|(?1))+\))#', function ($m) use ($me) {
			return $me->formatModifiers(substr($m[1], 1, -1));
		}, $mask);

		$args = func_get_args();
		$pos = $this->tokens->position;
		$word = strpos($mask, '%node_word') === FALSE ? NULL : $this->tokens->fetchWord();

		$code = preg_replace_callback('#([,+]\s*)?%(node_|\d+_|)(word|var|raw|array|args)(\?)?(\s*\+\s*)?()#',
		function ($m) use ($me, $word, & $args) {
			list(, $l, $source, $format, $cond, $r) = $m;

			switch ($source) {
				case 'node_':
					$arg = $word; break;
				case '':
					$arg = next($args); break;
				default:
					$arg = $args[$source + 1]; break;
			}

			switch ($format) {
				case 'word':
					$code = $me->formatWord($arg); break;
				case 'args':
					$code = $me->formatArgs(); break;
				case 'array':
					$code = $me->formatArray();
					$code = $cond && $code === 'array()' ? '' : $code; break;
				case 'var':
					$code = var_export($arg, TRUE); break;
				case 'raw':
					$code = (string) $arg; break;
			}

			if ($cond && $code === '') {
				return $r ? $l : $r;
			} else {
				return $l . $code . $r;
			}
		}, $mask);

		$this->tokens->position = $pos;
		return $code;
	}


	/**
	 * Formats modifiers calling.
	 * @param  string
	 * @return string
	 */
	public function formatModifiers($var)
	{
		$tokens = new MacroTokens(ltrim($this->modifiers, '|'));
		$tokens = $this->preprocess($tokens);
		$tokens = $this->modifiersFilter($tokens, $var);
		$tokens = $this->quoteFilter($tokens);
		return $tokens->joinAll();
	}


	/**
	 * Formats macro arguments to PHP code. (It advances tokenizer to the end as a side effect.)
	 * @return string
	 */
	public function formatArgs(MacroTokens $tokens = NULL)
	{
		$tokens = $this->preprocess($tokens);
		$tokens = $this->quoteFilter($tokens);
		return $tokens->joinAll();
	}


	/**
	 * Formats macro arguments to PHP array. (It advances tokenizer to the end as a side effect.)
	 * @return string
	 */
	public function formatArray(MacroTokens $tokens = NULL)
	{
		$tokens = $this->preprocess($tokens);
		$tokens = $this->expandFilter($tokens);
		$tokens = $this->quoteFilter($tokens);
		return $tokens->joinAll();
	}


	/**
	 * Formats parameter to PHP string.
	 * @param  string
	 * @return string
	 */
	public function formatWord($s)
	{
		return (is_numeric($s) || preg_match('#^\$|[\'"]|^true\z|^false\z|^null\z#i', $s))
			? $this->formatArgs(new MacroTokens($s))
			: '"' . $s . '"';
	}


	/**
	 * Preprocessor for tokens. (It advances tokenizer to the end as a side effect.)
	 * @return MacroTokens
	 */
	public function preprocess(MacroTokens $tokens = NULL)
	{
		$tokens = $tokens === NULL ? $this->tokens : $tokens;
		$tokens = $this->removeCommentsFilter($tokens);
		$tokens = $this->shortTernaryFilter($tokens);
		$tokens = $this->shortArraysFilter($tokens);
		return $tokens;
	}


	/**
	 * Removes PHP comments.
	 * @return MacroTokens
	 */
	public function removeCommentsFilter(MacroTokens $tokens)
	{
		$res = new MacroTokens;
		while ($tokens->nextToken()) {
			if (!$tokens->isCurrent(MacroTokens::T_COMMENT)) {
				$res->append($tokens->currentToken());
			}
		}
		return $res;
	}


	/**
	 * Simplified ternary expressions without third part.
	 * @return MacroTokens
	 */
	public function shortTernaryFilter(MacroTokens $tokens)
	{
		$res = new MacroTokens;
		$inTernary = array();
		while ($tokens->nextToken()) {
			if ($tokens->isCurrent('?')) {
				$inTernary[] = $tokens->depth;

			} elseif ($tokens->isCurrent(':')) {
				array_pop($inTernary);

			} elseif ($tokens->isCurrent(',', ')', ']') && end($inTernary) === $tokens->depth + !$tokens->isCurrent(',')) {
				$res->append(' : NULL');
				array_pop($inTernary);
			}
			$res->append($tokens->currentToken());
		}

		if ($inTernary) {
			$res->append(' : NULL');
		}
		return $res;
	}


	/**
	 * Simplified array syntax [...]
	 * @return MacroTokens
	 */
	public function shortArraysFilter(MacroTokens $tokens)
	{
		$res = new MacroTokens;
		$arrays = array();
		while ($tokens->nextToken()) {
			if ($tokens->isCurrent('[')) {
				if ($arrays[] = !$tokens->isPrev(']', ')', MacroTokens::T_SYMBOL, MacroTokens::T_VARIABLE, MacroTokens::T_KEYWORD)) {
					$res->append('array(');
					continue;

				}
			} elseif ($tokens->isCurrent(']')) {
				if (array_pop($arrays) === TRUE) {
					$res->append(')');
					continue;
				}
			}
			$res->append($tokens->currentToken());
		}
		return $res;
	}


	/**
	 * Pseudocast (expand).
	 * @return MacroTokens
	 */
	public function expandFilter(MacroTokens $tokens)
	{
		$res = new MacroTokens('array(');
		$expand = NULL;
		while ($tokens->nextToken()) {
			if ($tokens->isCurrent('(expand)') && $tokens->depth === 0) {
				$expand = TRUE;
				$res->append('),');
			} elseif ($expand && $tokens->isCurrent(',') && !$tokens->depth) {
				$expand = FALSE;
				$res->append(', array(');
			} else {
				$res->append($tokens->currentToken());
			}
		}

		if ($expand !== NULL) {
			$res->prepend('array_merge(')->append($expand ? ', array()' : ')');
		}
		return $res->append(')');
	}


	/**
	 * Quotes symbols to strings.
	 * @return MacroTokens
	 */
	public function quoteFilter(MacroTokens $tokens)
	{
		$res = new MacroTokens;
		while ($tokens->nextToken()) {
			$res->append($tokens->isCurrent(MacroTokens::T_SYMBOL)
				&& (!$tokens->isPrev() || $tokens->isPrev(',', '(', '[', '=>', ':', '?', '.', '<', '>', '<=', '>=', '===', '!==', '==', '!=', '<>', '&&', '||', '=', 'and', 'or', 'xor'))
				&& (!$tokens->isNext() || $tokens->isNext(',', ';', ')', ']', '=>', ':', '?', '.', '<', '>', '<=', '>=', '===', '!==', '==', '!=', '<>', '&&', '||', 'and', 'or', 'xor'))
				? "'" . $tokens->currentValue() . "'"
				: $tokens->currentToken()
			);
		}
		return $res;
	}


	/**
	 * Formats modifiers calling.
	 * @param  MacroTokens
	 * @param  string
	 * @throws CompileException
	 * @return MacroTokens
	 */
	public function modifiersFilter(MacroTokens $tokens, $var)
	{
		$inside = FALSE;
		$res = new MacroTokens($var);
		while ($tokens->nextToken()) {
			if ($tokens->isCurrent(MacroTokens::T_WHITESPACE)) {
				$res->append(' ');

			} elseif ($inside) {
				if ($tokens->isCurrent(':', ',')) {
					$res->append(', ');
					$tokens->nextAll(MacroTokens::T_WHITESPACE);

				} elseif ($tokens->isCurrent('|')) {
					$res->append(')');
					$inside = FALSE;

				} else {
					$res->append($tokens->currentToken());
				}
			} else {
				if ($tokens->isCurrent(MacroTokens::T_SYMBOL)) {
					if ($this->compiler && $tokens->isCurrent('escape')) {
						$res = $this->escapeFilter($res);
						$tokens->nextToken('|');
					} elseif (!strcasecmp($tokens->currentValue(), 'safeurl')) {
						$res->prepend('Latte\Runtime\Filters::safeUrl(');
						$inside = TRUE;
					} else {
						$res->prepend('$template->' . $tokens->currentValue() . '(');
						$inside = TRUE;
					}
				} else {
					throw new CompileException("Modifier name must be alphanumeric string, '{$tokens->currentValue()}' given.");
				}
			}
		}
		if ($inside) {
			$res->append(')');
		}
		return $res;
	}


	/**
	 * Escapes expression in tokens.
	 * @return MacroTokens
	 */
	public function escapeFilter(MacroTokens $tokens)
	{
		$tokens = clone $tokens;
		switch ($this->compiler->getContentType()) {
			case Compiler::CONTENT_XHTML:
			case Compiler::CONTENT_HTML:
				$context = $this->compiler->getContext();
				switch ($context[0]) {
					case Compiler::CONTEXT_SINGLE_QUOTED_ATTR:
					case Compiler::CONTEXT_DOUBLE_QUOTED_ATTR:
					case Compiler::CONTEXT_UNQUOTED_ATTR:
						if ($context[1] === Compiler::CONTENT_JS) {
							$tokens->prepend('Latte\Runtime\Filters::escapeJs(')->append(')');
						} elseif ($context[1] === Compiler::CONTENT_CSS) {
							$tokens->prepend('Latte\Runtime\Filters::escapeCss(')->append(')');
						}
						$tokens->prepend('Latte\Runtime\Filters::escapeHtml(')->append($context[0] === Compiler::CONTEXT_SINGLE_QUOTED_ATTR ? ', ENT_QUOTES)' : ', ENT_COMPAT)');
						if ($context[0] === Compiler::CONTEXT_UNQUOTED_ATTR) {
							$tokens->prepend("'\"', ")->append(", '\"'");
						}
						return $tokens;
					case Compiler::CONTEXT_COMMENT:
						return $tokens->prepend('Latte\Runtime\Filters::escapeHtmlComment(')->append(')');
					case Compiler::CONTENT_JS:
					case Compiler::CONTENT_CSS:
						return $tokens->prepend('Latte\Runtime\Filters::escape' . ucfirst($context[0]) . '(')->append(')');
					default:
						return $tokens->prepend('Latte\Runtime\Filters::escapeHtml(')->append(', ENT_NOQUOTES)');
				}

			case Compiler::CONTENT_XML:
				$context = $this->compiler->getContext();
				switch ($context[0]) {
					case Compiler::CONTEXT_COMMENT:
						return $tokens->prepend('Latte\Runtime\Filters::escapeHtmlComment(')->append(')');
					default:
						$tokens->prepend('Latte\Runtime\Filters::escapeXml(')->append(')');
						if ($context[0] === Compiler::CONTEXT_UNQUOTED_ATTR) {
							$tokens->prepend("'\"', ")->append(", '\"'");
						}
						return $tokens;
				}

			case Compiler::CONTENT_JS:
			case Compiler::CONTENT_CSS:
			case Compiler::CONTENT_ICAL:
				return $tokens->prepend('Latte\Runtime\Filters::escape' . ucfirst($this->compiler->getContentType()) . '(')->append(')');
			case Compiler::CONTENT_TEXT:
				return $tokens;
			default:
				return $tokens->prepend('$template->escape(')->append(')');
		}
	}

}
