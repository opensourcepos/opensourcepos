<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * Latte compiler.
 */
class Compiler extends Object
{
	/** @var Token[] */
	private $tokens;

	/** @var string pointer to current node content */
	private $output;

	/** @var int  position on source template */
	private $position;

	/** @var array of [name => IMacro[]] */
	private $macros;

	/** @var \SplObjectStorage */
	private $macroHandlers;

	/** @var HtmlNode */
	private $htmlNode;

	/** @var MacroNode */
	private $macroNode;

	/** @var string[] */
	private $attrCodes = array();

	/** @var string */
	private $contentType;

	/** @var array [context, subcontext] */
	private $context;

	/** @var string */
	private $templateId;

	/** @var mixed */
	private $lastAttrValue;

	/** Context-aware escaping content types */
	const CONTENT_HTML = Engine::CONTENT_HTML,
		CONTENT_XHTML = Engine::CONTENT_XHTML,
		CONTENT_XML = Engine::CONTENT_XML,
		CONTENT_JS = Engine::CONTENT_JS,
		CONTENT_CSS = Engine::CONTENT_CSS,
		CONTENT_URL = Engine::CONTENT_URL,
		CONTENT_ICAL = Engine::CONTENT_ICAL,
		CONTENT_TEXT = Engine::CONTENT_TEXT;

	/** @internal Context-aware escaping HTML contexts */
	const CONTEXT_COMMENT = 'comment',
		CONTEXT_SINGLE_QUOTED_ATTR = "'",
		CONTEXT_DOUBLE_QUOTED_ATTR = '"',
		CONTEXT_UNQUOTED_ATTR = '=';


	public function __construct()
	{
		$this->macroHandlers = new \SplObjectStorage;
	}


	/**
	 * Adds new macro.
	 * @param  string
	 * @return self
	 */
	public function addMacro($name, IMacro $macro)
	{
		$this->macros[$name][] = $macro;
		$this->macroHandlers->attach($macro);
		return $this;
	}


	/**
	 * Compiles tokens to PHP code.
	 * @param  Token[]
	 * @return string
	 */
	public function compile(array $tokens, $className)
	{
		$this->templateId = substr(md5($className), 0, 10);
		$this->tokens = $tokens;
		$output = '';
		$this->output = & $output;
		$this->htmlNode = $this->macroNode = $this->context = NULL;

		foreach ($this->macroHandlers as $handler) {
			$handler->initialize($this);
		}

		foreach ($tokens as $this->position => $token) {
			$this->{"process$token->type"}($token);
		}

		while ($this->htmlNode) {
			if (!empty($this->htmlNode->macroAttrs)) {
				throw new CompileException('Missing ' . self::printEndTag($this->macroNode));
			}
			$this->htmlNode = $this->htmlNode->parentNode;
		}

		$prologs = $epilogs = '';
		foreach ($this->macroHandlers as $handler) {
			$res = $handler->finalize();
			$handlerName = get_class($handler);
			$prologs .= empty($res[0]) ? '' : "<?php\n// prolog $handlerName\n$res[0]\n?>";
			$epilogs = (empty($res[1]) ? '' : "<?php\n// epilog $handlerName\n$res[1]\n?>") . $epilogs;
		}
		$output = ($prologs ? $prologs . "<?php\n//\n// main template\n//\n?>\n" : '') . $output . $epilogs;

		if ($this->macroNode) {
			throw new CompileException('Missing ' . self::printEndTag($this->macroNode));
		}

		$output = $this->expandTokens($output);
		$output = "<?php\n"
			. "class $className extends Latte\\Template {\n"
			. "function render() {\n"
			. 'foreach ($this->params as $__k => $__v) $$__k = $__v; unset($__k, $__v);'
			. '?>' . $output . "<?php\n}}";

		return $output;
	}


	/**
	 * @return self
	 */
	public function setContentType($type)
	{
		$this->contentType = $type;
		$this->context = NULL;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}


	/**
	 * @return self
	 */
	public function setContext($context, $sub = NULL)
	{
		$this->context = array($context, $sub);
		return $this;
	}


	/**
	 * @return array [context, subcontext]
	 */
	public function getContext()
	{
		return $this->context;
	}


	/**
	 * @return string
	 */
	public function getTemplateId()
	{
		return $this->templateId;
	}


	/**
	 * @return MacroNode|NULL
	 */
	public function getMacroNode()
	{
		return $this->macroNode;
	}


	/**
	 * Returns current line number.
	 * @return int
	 */
	public function getLine()
	{
		return $this->tokens ? $this->tokens[$this->position]->line : NULL;
	}


	/** @internal */
	public function expandTokens($s)
	{
		return strtr($s, $this->attrCodes);
	}


	private function processText(Token $token)
	{
		if (in_array($this->context[0], array(self::CONTEXT_SINGLE_QUOTED_ATTR, self::CONTEXT_DOUBLE_QUOTED_ATTR), TRUE)) {
			if ($token->text === $this->context[0]) {
				$this->setContext(self::CONTEXT_UNQUOTED_ATTR);
			} elseif ($this->lastAttrValue === '') {
				$this->lastAttrValue = $token->text;
			}
		}
		$this->output .= $token->text;
	}


	private function processMacroTag(Token $token)
	{
		if (in_array($this->context[0], array(self::CONTEXT_SINGLE_QUOTED_ATTR, self::CONTEXT_DOUBLE_QUOTED_ATTR, self::CONTEXT_UNQUOTED_ATTR), TRUE)) {
			$this->lastAttrValue = TRUE;
		}

		$isRightmost = !isset($this->tokens[$this->position + 1])
			|| substr($this->tokens[$this->position + 1]->text, 0, 1) === "\n";

		if ($token->name[0] === '/') {
			$this->closeMacro((string) substr($token->name, 1), $token->value, $token->modifiers, $isRightmost);
		} else {
			$this->openMacro($token->name, $token->value, $token->modifiers, $isRightmost && !$token->empty);
			if ($token->empty) {
				$this->closeMacro($token->name, NULL, NULL, $isRightmost);
			}
		}
	}


	private function processHtmlTagBegin(Token $token)
	{
		if ($token->closing) {
			while ($this->htmlNode) {
				if (strcasecmp($this->htmlNode->name, $token->name) === 0) {
					break;
				}
				if ($this->htmlNode->macroAttrs) {
					throw new CompileException("Unexpected </$token->name>, expecting " . self::printEndTag($this->macroNode));
				}
				$this->htmlNode = $this->htmlNode->parentNode;
			}
			if (!$this->htmlNode) {
				$this->htmlNode = new HtmlNode($token->name);
			}
			$this->htmlNode->closing = TRUE;
			$this->htmlNode->offset = strlen($this->output);
			$this->setContext(NULL);

		} elseif ($token->text === '<!--') {
			$this->setContext(self::CONTEXT_COMMENT);

		} else {
			$this->htmlNode = new HtmlNode($token->name, $this->htmlNode);
			$this->htmlNode->offset = strlen($this->output);
			$this->setContext(self::CONTEXT_UNQUOTED_ATTR);
		}
		$this->output .= $token->text;
	}


	private function processHtmlTagEnd(Token $token)
	{
		if ($token->text === '-->') {
			$this->output .= $token->text;
			$this->setContext(NULL);
			return;
		}

		$htmlNode = $this->htmlNode;
		$end = '';

		if (!$htmlNode->closing) {
			$htmlNode->isEmpty = strpos($token->text, '/') !== FALSE;
			if (in_array($this->contentType, array(self::CONTENT_HTML, self::CONTENT_XHTML), TRUE)) {
				$emptyElement = isset(Helpers::$emptyElements[strtolower($htmlNode->name)]);
				$htmlNode->isEmpty = $htmlNode->isEmpty || $emptyElement;
				if ($htmlNode->isEmpty) { // auto-correct
					$space = substr(strstr($token->text, '>'), 1);
					if ($emptyElement) {
						$token->text = ($this->contentType === self::CONTENT_XHTML ? ' />' : '>') . $space;
					} else {
						$token->text = '>';
						$end = "</$htmlNode->name>" . $space;
					}
				}
			}
		}

		if ($htmlNode->macroAttrs) {
			$code = substr($this->output, $htmlNode->offset) . $token->text;
			$this->output = substr($this->output, 0, $htmlNode->offset);
			$this->writeAttrsMacro($code);
		} else {
			$this->output .= $token->text . $end;
		}

		if ($htmlNode->isEmpty) {
			$htmlNode->closing = TRUE;
			if ($htmlNode->macroAttrs) {
				$this->writeAttrsMacro($end);
			}
		}

		$this->setContext(NULL);

		if ($htmlNode->closing) {
			$this->htmlNode = $this->htmlNode->parentNode;

		} elseif ((($lower = strtolower($htmlNode->name)) === 'script' || $lower === 'style')
			&& (!isset($htmlNode->attrs['type']) || preg_match('#(java|j|ecma|live)script|json|css#i', $htmlNode->attrs['type']))
		) {
			$this->setContext($lower === 'script' ? self::CONTENT_JS : self::CONTENT_CSS);
		}
	}


	private function processHtmlAttribute(Token $token)
	{
		if (strncmp($token->name, Parser::N_PREFIX, strlen(Parser::N_PREFIX)) === 0) {
			$name = substr($token->name, strlen(Parser::N_PREFIX));
			if (isset($this->htmlNode->macroAttrs[$name])) {
				throw new CompileException("Found multiple attributes $token->name.");

			} elseif ($this->macroNode && $this->macroNode->htmlNode === $this->htmlNode) {
				throw new CompileException("n:attributes must not appear inside macro; found $token->name inside {{$this->macroNode->name}}.");
			}
			$this->htmlNode->macroAttrs[$name] = $token->value;
			return;
		}

		$this->lastAttrValue = & $this->htmlNode->attrs[$token->name];
		$this->output .= $token->text;

		if (in_array($token->value, array(self::CONTEXT_SINGLE_QUOTED_ATTR, self::CONTEXT_DOUBLE_QUOTED_ATTR), TRUE)) {
			$this->lastAttrValue = '';
			$contextMain = $token->value;
		} else {
			$this->lastAttrValue = $token->value;
			$contextMain = self::CONTEXT_UNQUOTED_ATTR;
		}

		$context = NULL;
		if (in_array($this->contentType, array(self::CONTENT_HTML, self::CONTENT_XHTML), TRUE)) {
			$lower = strtolower($token->name);
			if (substr($lower, 0, 2) === 'on') {
				$context = self::CONTENT_JS;
			} elseif ($lower === 'style') {
				$context = self::CONTENT_CSS;
			} elseif (in_array($lower, array('href', 'src', 'action', 'formaction'), TRUE)
				|| ($lower === 'data' && strtolower($this->htmlNode->name) === 'object')
			) {
				$context = self::CONTENT_URL;
			}
		}

		$this->setContext($contextMain, $context);
	}


	private function processComment(Token $token)
	{
		$isLeftmost = trim(substr($this->output, strrpos("\n$this->output", "\n"))) === '';
		if (!$isLeftmost) {
			$this->output .= substr($token->text, strlen(rtrim($token->text, "\n")));
		}
	}


	/********************* macros ****************d*g**/


	/**
	 * Generates code for {macro ...} to the output.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return MacroNode
	 * @internal
	 */
	public function openMacro($name, $args = NULL, $modifiers = NULL, $isRightmost = FALSE, $nPrefix = NULL)
	{
		$node = $this->expandMacro($name, $args, $modifiers, $nPrefix);
		if ($node->isEmpty) {
			$this->writeCode($node->openingCode, $this->output, $node->replaced, $isRightmost);
		} else {
			$this->macroNode = $node;
			$node->saved = array(& $this->output, $isRightmost);
			$this->output = & $node->content;
		}
		return $node;
	}


	/**
	 * Generates code for {/macro ...} to the output.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return MacroNode
	 * @internal
	 */
	public function closeMacro($name, $args = NULL, $modifiers = NULL, $isRightmost = FALSE, $nPrefix = NULL)
	{
		$node = $this->macroNode;

		if (!$node || ($node->name !== $name && '' !== $name) || $modifiers
			|| ($args && $node->args && strncmp("$node->args ", "$args ", strlen($args) + 1))
			|| $nPrefix !== $node->prefix
		) {
			$name = $nPrefix
				? "</{$this->htmlNode->name}> for " . Parser::N_PREFIX . implode(' and ' . Parser::N_PREFIX, array_keys($this->htmlNode->macroAttrs))
				: '{/' . $name . ($args ? ' ' . $args : '') . $modifiers . '}';
			throw new CompileException("Unexpected $name" . ($node ? ', expecting ' . self::printEndTag($node) : ''));
		}

		$this->macroNode = $node->parentNode;
		if (!$node->args) {
			$node->setArgs($args);
		}

		$isLeftmost = $node->content ? trim(substr($this->output, strrpos("\n$this->output", "\n"))) === '' : FALSE;

		$node->closing = TRUE;
		$node->macro->nodeClosed($node);

		$this->output = & $node->saved[0];
		$this->writeCode($node->openingCode, $this->output, $node->replaced, $node->saved[1]);
		$this->writeCode($node->closingCode, $node->content, $node->replaced, $isRightmost, $isLeftmost);
		$this->output .= $node->content;
		return $node;
	}


	private function writeCode($code, & $output, $replaced, $isRightmost, $isLeftmost = NULL)
	{
		if ($isRightmost) {
			$leftOfs = strrpos("\n$output", "\n");
			if ($isLeftmost === NULL) {
				$isLeftmost = trim(substr($output, $leftOfs)) === '';
			}
			if ($replaced === NULL) {
				$replaced = preg_match('#<\?php.*\secho\s#As', $code);
			}
			if ($isLeftmost && !$replaced) {
				$output = substr($output, 0, $leftOfs); // alone macro without output -> remove indentation
			} elseif (substr($code, -2) === '?>') {
				$code .= "\n"; // double newline to avoid newline eating by PHP
			}
		}
		$output .= $code;
	}


	/**
	 * Generates code for macro <tag n:attr> to the output.
	 * @param  string
	 * @return void
	 * @internal
	 */
	public function writeAttrsMacro($code)
	{
		$attrs = $this->htmlNode->macroAttrs;
		$left = $right = array();

		foreach ($this->macros as $name => $foo) {
			$attrName = MacroNode::PREFIX_INNER . "-$name";
			if (isset($attrs[$attrName])) {
				if ($this->htmlNode->closing) {
					$left[] = array('closeMacro', $name, '', MacroNode::PREFIX_INNER);
				} else {
					array_unshift($right, array('openMacro', $name, $attrs[$attrName], MacroNode::PREFIX_INNER));
				}
				unset($attrs[$attrName]);
			}
		}

		foreach (array_reverse($this->macros) as $name => $foo) {
			$attrName = MacroNode::PREFIX_TAG . "-$name";
			if (isset($attrs[$attrName])) {
				$left[] = array('openMacro', $name, $attrs[$attrName], MacroNode::PREFIX_TAG);
				array_unshift($right, array('closeMacro', $name, '', MacroNode::PREFIX_TAG));
				unset($attrs[$attrName]);
			}
		}

		foreach ($this->macros as $name => $foo) {
			if (isset($attrs[$name])) {
				if ($this->htmlNode->closing) {
					$right[] = array('closeMacro', $name, '', MacroNode::PREFIX_NONE);
				} else {
					array_unshift($left, array('openMacro', $name, $attrs[$name], MacroNode::PREFIX_NONE));
				}
				unset($attrs[$name]);
			}
		}

		if ($attrs) {
			throw new CompileException('Unknown attribute ' . Parser::N_PREFIX
				. implode(' and ' . Parser::N_PREFIX, array_keys($attrs)));
		}

		if (!$this->htmlNode->closing) {
			$this->htmlNode->attrCode = & $this->attrCodes[$uniq = ' n:' . substr(lcg_value(), 2, 10)];
			$code = substr_replace($code, $uniq, strrpos($code, '/>') ?: strrpos($code, '>'), 0);
		}

		foreach ($left as $item) {
			$node = $this->{$item[0]}($item[1], $item[2], NULL, NULL, $item[3]);
			if ($node->closing || $node->isEmpty) {
				$this->htmlNode->attrCode .= $node->attrCode;
				if ($node->isEmpty) {
					unset($this->htmlNode->macroAttrs[$node->name]);
				}
			}
		}

		$this->output .= $code;

		foreach ($right as $item) {
			$node = $this->{$item[0]}($item[1], $item[2], NULL, NULL, $item[3]);
			if ($node->closing) {
				$this->htmlNode->attrCode .= $node->attrCode;
			}
		}

		if ($right && substr($this->output, -2) === '?>') {
			$this->output .= "\n";
		}
	}


	/**
	 * Expands macro and returns node & code.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return MacroNode
	 * @internal
	 */
	public function expandMacro($name, $args, $modifiers = NULL, $nPrefix = NULL)
	{
		$inScript = in_array($this->context[0], array(self::CONTENT_JS, self::CONTENT_CSS), TRUE);

		if (empty($this->macros[$name])) {
			throw new CompileException("Unknown macro {{$name}}" . ($inScript ? ' (in JavaScript or CSS, try to put a space after bracket.)' : ''));
		}

		if ($this->context[1] === self::CONTENT_URL) {
			$modifiers = preg_replace('#\|nosafeurl\s?(?=\||\z)#i', '', $modifiers, -1, $found);
			if (!$found && !preg_match('#\|datastream(?=\s|\||\z)#i', $modifiers)) {
				$modifiers .= '|safeurl';
			}
		}

		$modifiers = preg_replace('#\|noescape\s?(?=\||\z)#i', '', $modifiers, -1, $found);
		if (!$found && strpbrk($name, '=~%^&_')) {
			$modifiers .= '|escape';
		}

		if (!$found && $inScript && $name === '=' && preg_match('#["\'] *\z#', $this->tokens[$this->position - 1]->text)) {
			throw new CompileException("Do not place {$this->tokens[$this->position]->text} inside quotes.");
		}

		foreach (array_reverse($this->macros[$name]) as $macro) {
			$node = new MacroNode($macro, $name, $args, $modifiers, $this->macroNode, $this->htmlNode, $nPrefix);
			if ($macro->nodeOpened($node) !== FALSE) {
				return $node;
			}
		}

		throw new CompileException('Unknown ' . ($nPrefix
			? 'attribute ' . Parser::N_PREFIX . ($nPrefix === MacroNode::PREFIX_NONE ? '' : "$nPrefix-") . $name
			: 'macro {' . $name . ($args ? " $args" : '') . '}'
		));
	}


	private static function printEndTag(MacroNode $node)
	{
		if ($node->prefix) {
			return  "</{$node->htmlNode->name}> for " . Parser::N_PREFIX
				. implode(' and ' . Parser::N_PREFIX, array_keys($node->htmlNode->macroAttrs));
		} else {
			return "{/$node->name}";
		}
	}

}
