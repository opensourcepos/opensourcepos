<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte;


/**
 * Latte parser.
 */
class Parser extends Object
{
	/** @internal regular expression for single & double quoted PHP string */
	const RE_STRING = '\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';

	/** @internal special HTML attribute prefix */
	const N_PREFIX = 'n:';

	/** @var string default macro tag syntax */
	public $defaultSyntax = 'latte';

	/** @var bool */
	public $shortNoEscape = FALSE;

	/** @var array */
	public $syntaxes = array(
		'latte' => array('\\{(?![\\s\'"{}])', '\\}'), // {...}
		'double' => array('\\{\\{(?![\\s\'"{}])', '\\}\\}'), // {{...}}
		'asp' => array('<%\s*', '\s*%>'), /* <%...%> */
		'python' => array('\\{[{%]\s*', '\s*[%}]\\}'), // {% ... %} | {{ ... }}
		'off' => array('[^\x00-\xFF]', ''),
	);

	/** @var string[] */
	private $delimiters;

	/** @var string source template */
	private $input;

	/** @var Token[] */
	private $output;

	/** @var int  position on source template */
	private $offset;

	/** @var array */
	private $context;

	/** @var string */
	private $lastHtmlTag;

	/** @var string used by filter() */
	private $syntaxEndTag;

	/** @var int */
	private $syntaxEndLevel = 0;

	/** @var bool */
	private $xmlMode;

	/** @internal states */
	const CONTEXT_HTML_TEXT = 'htmlText',
		CONTEXT_CDATA = 'cdata',
		CONTEXT_HTML_TAG = 'htmlTag',
		CONTEXT_HTML_ATTRIBUTE = 'htmlAttribute',
		CONTEXT_RAW = 'raw',
		CONTEXT_HTML_COMMENT = 'htmlComment',
		CONTEXT_MACRO = 'macro';


	/**
	 * Process all {macros} and <tags/>.
	 * @param  string
	 * @return Token[]
	 */
	public function parse($input)
	{
		if (substr($input, 0, 3) === "\xEF\xBB\xBF") { // BOM
			$input = substr($input, 3);
		}
		if (!preg_match('##u', $input)) {
			throw new \InvalidArgumentException('Template is not valid UTF-8 stream.');
		}
		$input = str_replace("\r\n", "\n", $input);
		$this->input = $input;
		$this->output = array();
		$this->offset = $tokenCount = 0;

		$this->setSyntax($this->defaultSyntax);
		$this->setContext(self::CONTEXT_HTML_TEXT);
		$this->lastHtmlTag = $this->syntaxEndTag = NULL;

		while ($this->offset < strlen($input)) {
			if ($this->{'context' . $this->context[0]}() === FALSE) {
				break;
			}
			while ($tokenCount < count($this->output)) {
				$this->filter($this->output[$tokenCount++]);
			}
		}
		if ($this->context[0] === self::CONTEXT_MACRO) {
			throw new CompileException('Malformed macro');
		}

		if ($this->offset < strlen($input)) {
			$this->addToken(Token::TEXT, substr($this->input, $this->offset));
		}
		return $this->output;
	}


	/**
	 * Handles CONTEXT_HTML_TEXT.
	 */
	private function contextHtmlText()
	{
		$matches = $this->match('~
			(?:(?<=\n|^)[ \t]*)?<(?P<closing>/?)(?P<tag>[a-z0-9:]+)|  ##  begin of HTML tag <tag </tag - ignores <!DOCTYPE
			<(?P<htmlcomment>!--(?!>))|     ##  begin of HTML comment <!--, but not <!-->
			(?P<macro>' . $this->delimiters[0] . ')
		~xsi');

		if (!empty($matches['htmlcomment'])) { // <!--
			$this->addToken(Token::HTML_TAG_BEGIN, $matches[0]);
			$this->setContext(self::CONTEXT_HTML_COMMENT);

		} elseif (!empty($matches['tag'])) { // <tag or </tag
			$token = $this->addToken(Token::HTML_TAG_BEGIN, $matches[0]);
			$token->name = $matches['tag'];
			$token->closing = (bool) $matches['closing'];
			$this->lastHtmlTag = $matches['closing'] . strtolower($matches['tag']);
			$this->setContext(self::CONTEXT_HTML_TAG);

		} else {
			return $this->processMacro($matches);
		}
	}


	/**
	 * Handles CONTEXT_CDATA.
	 */
	private function contextCData()
	{
		$matches = $this->match('~
			</(?P<tag>' . $this->lastHtmlTag . ')(?![a-z0-9:])| ##  end HTML tag </tag
			(?P<macro>' . $this->delimiters[0] . ')
		~xsi');

		if (!empty($matches['tag'])) { // </tag
			$token = $this->addToken(Token::HTML_TAG_BEGIN, $matches[0]);
			$token->name = $this->lastHtmlTag;
			$token->closing = TRUE;
			$this->lastHtmlTag = '/' . $this->lastHtmlTag;
			$this->setContext(self::CONTEXT_HTML_TAG);
		} else {
			return $this->processMacro($matches);
		}
	}


	/**
	 * Handles CONTEXT_HTML_TAG.
	 */
	private function contextHtmlTag()
	{
		$matches = $this->match('~
			(?P<end>\ ?/?>)([ \t]*\n)?|  ##  end of HTML tag
			(?P<macro>' . $this->delimiters[0] . ')|
			\s*(?P<attr>[^\s/>={]+)(?:\s*=\s*(?P<value>["\']|[^\s/>{]+))? ## beginning of HTML attribute
		~xsi');

		if (!empty($matches['end'])) { // end of HTML tag />
			$this->addToken(Token::HTML_TAG_END, $matches[0]);
			$this->setContext(!$this->xmlMode && in_array($this->lastHtmlTag, array('script', 'style'), TRUE) ? self::CONTEXT_CDATA : self::CONTEXT_HTML_TEXT);

		} elseif (isset($matches['attr']) && $matches['attr'] !== '') { // HTML attribute
			$token = $this->addToken(Token::HTML_ATTRIBUTE, $matches[0]);
			$token->name = $matches['attr'];
			$token->value = isset($matches['value']) ? $matches['value'] : '';

			if ($token->value === '"' || $token->value === "'") { // attribute = "'
				if (strncmp($token->name, self::N_PREFIX, strlen(self::N_PREFIX)) === 0) {
					$token->value = '';
					if ($m = $this->match('~(.*?)' . $matches['value'] . '~xsi')) {
						$token->value = $m[1];
						$token->text .= $m[0];
					}
				} else {
					$this->setContext(self::CONTEXT_HTML_ATTRIBUTE, $matches['value']);
				}
			}
		} else {
			return $this->processMacro($matches);
		}
	}


	/**
	 * Handles CONTEXT_HTML_ATTRIBUTE.
	 */
	private function contextHtmlAttribute()
	{
		$matches = $this->match('~
			(?P<quote>' . $this->context[1] . ')|  ##  end of HTML attribute
			(?P<macro>' . $this->delimiters[0] . ')
		~xsi');

		if (!empty($matches['quote'])) { // (attribute end) '"
			$this->addToken(Token::TEXT, $matches[0]);
			$this->setContext(self::CONTEXT_HTML_TAG);
		} else {
			return $this->processMacro($matches);
		}
	}


	/**
	 * Handles CONTEXT_HTML_COMMENT.
	 */
	private function contextHtmlComment()
	{
		$matches = $this->match('~
			(?P<htmlcomment>-->)|   ##  end of HTML comment
			(?P<macro>' . $this->delimiters[0] . ')
		~xsi');

		if (!empty($matches['htmlcomment'])) { // -->
			$this->addToken(Token::HTML_TAG_END, $matches[0]);
			$this->setContext(self::CONTEXT_HTML_TEXT);
		} else {
			return $this->processMacro($matches);
		}
	}


	/**
	 * Handles CONTEXT_RAW.
	 */
	private function contextRaw()
	{
		$matches = $this->match('~
			(?P<macro>' . $this->delimiters[0] . ')
		~xsi');
		return $this->processMacro($matches);
	}


	/**
	 * Handles CONTEXT_MACRO.
	 */
	private function contextMacro()
	{
		$matches = $this->match('~
			(?P<comment>\\*.*?\\*' . $this->delimiters[1] . '\n{0,2})|
			(?P<macro>(?:
				' . self::RE_STRING . '|
				\{(?:' . self::RE_STRING . '|[^\'"{}])*+\}|
				[^\'"{}]
			)+?)
			' . $this->delimiters[1] . '
			(?P<rmargin>[ \t]*(?=\n))?
		~xsiA');

		if (!empty($matches['macro'])) {
			$token = $this->addToken(Token::MACRO_TAG, $this->context[1][1] . $matches[0]);
			list($token->name, $token->value, $token->modifiers, $token->empty) = $this->parseMacroTag($matches['macro']);
			$this->context = $this->context[1][0];

		} elseif (!empty($matches['comment'])) {
			$this->addToken(Token::COMMENT, $this->context[1][1] . $matches[0]);
			$this->context = $this->context[1][0];

		} else {
			throw new CompileException('Malformed macro');
		}
	}


	private function processMacro($matches)
	{
		if (!empty($matches['macro'])) { // {macro} or {* *}
			$this->setContext(self::CONTEXT_MACRO, array($this->context, $matches['macro']));
		} else {
			return FALSE;
		}
	}


	/**
	 * Matches next token.
	 * @param  string
	 * @return array
	 */
	private function match($re)
	{
		if (!preg_match($re, $this->input, $matches, PREG_OFFSET_CAPTURE, $this->offset)) {
			if (preg_last_error()) {
				throw new RegexpException(NULL, preg_last_error());
			}
			return array();
		}

		$value = substr($this->input, $this->offset, $matches[0][1] - $this->offset);
		if ($value !== '') {
			$this->addToken(Token::TEXT, $value);
		}
		$this->offset = $matches[0][1] + strlen($matches[0][0]);
		foreach ($matches as $k => $v) {
			$matches[$k] = $v[0];
		}
		return $matches;
	}


	/**
	 * @return self
	 */
	public function setContentType($type)
	{
		if (strpos($type, 'html') !== FALSE) {
			$this->xmlMode = FALSE;
			$this->setContext(self::CONTEXT_HTML_TEXT);
		} elseif (strpos($type, 'xml') !== FALSE) {
			$this->xmlMode = TRUE;
			$this->setContext(self::CONTEXT_HTML_TEXT);
		} else {
			$this->setContext(self::CONTEXT_RAW);
		}
		return $this;
	}


	/**
	 * @return self
	 */
	public function setContext($context, $quote = NULL)
	{
		$this->context = array($context, $quote);
		return $this;
	}


	/**
	 * Changes macro tag delimiters.
	 * @param  string
	 * @return self
	 */
	public function setSyntax($type)
	{
		$type = $type ?: $this->defaultSyntax;
		if (isset($this->syntaxes[$type])) {
			$this->setDelimiters($this->syntaxes[$type][0], $this->syntaxes[$type][1]);
		} else {
			throw new \InvalidArgumentException("Unknown syntax '$type'");
		}
		return $this;
	}


	/**
	 * Changes macro tag delimiters.
	 * @param  string  left regular expression
	 * @param  string  right regular expression
	 * @return self
	 */
	public function setDelimiters($left, $right)
	{
		$this->delimiters = array($left, $right);
		return $this;
	}


	/**
	 * Parses macro tag to name, arguments a modifiers parts.
	 * @param  string {name arguments | modifiers}
	 * @return array
	 * @internal
	 */
	public function parseMacroTag($tag)
	{
		if (!preg_match('~^
			(
				(?P<name>\?|/?[a-z]\w*+(?:[.:]\w+)*+(?!::|\(|\\\\))|   ## ?, name, /name, but not function( or class:: or namespace\
				(?P<noescape>!?)(?P<shortname>/?[=\~#%^&_]?)      ## !expression, !=expression, ...
			)(?P<args>.*?)
			(?P<modifiers>\|[a-z](?:' . self::RE_STRING . '|[^\'"])*(?<!/))?
			(?P<empty>/?\z)
		()\z~isx', $tag, $match)) {
			if (preg_last_error()) {
				throw new RegexpException(NULL, preg_last_error());
			}
			return FALSE;
		}
		if ($match['name'] === '') {
			$match['name'] = $match['shortname'] ?: '=';
			if ($match['noescape']) {
				if (!$this->shortNoEscape) {
					trigger_error("The noescape shortcut {!...} is deprecated, use {...|noescape} modifier on line {$this->getLine()}.", E_USER_DEPRECATED);
				}
				$match['modifiers'] .= '|noescape';
			}
		}
		return array($match['name'], trim($match['args']), $match['modifiers'], (bool) $match['empty']);
	}


	private function addToken($type, $text)
	{
		$this->output[] = $token = new Token;
		$token->type = $type;
		$token->text = $text;
		$token->line = $this->getLine();
		return $token;
	}


	public function getLine()
	{
		return substr_count($this->input, "\n", 0, max(1, $this->offset - 1)) + 1;
	}


	/**
	 * Process low-level macros.
	 */
	protected function filter(Token $token)
	{
		if ($token->type === Token::MACRO_TAG && $token->name === '/syntax') {
			$this->setSyntax($this->defaultSyntax);
			$token->type = Token::COMMENT;

		} elseif ($token->type === Token::MACRO_TAG && $token->name === 'syntax') {
			$this->setSyntax($token->value);
			$token->type = Token::COMMENT;

		} elseif ($token->type === Token::HTML_ATTRIBUTE && $token->name === 'n:syntax') {
			$this->setSyntax($token->value);
			$this->syntaxEndTag = $this->lastHtmlTag;
			$this->syntaxEndLevel = 1;
			$token->type = Token::COMMENT;

		} elseif ($token->type === Token::HTML_TAG_BEGIN && $this->lastHtmlTag === $this->syntaxEndTag) {
			$this->syntaxEndLevel++;

		} elseif ($token->type === Token::HTML_TAG_END && $this->lastHtmlTag === ('/' . $this->syntaxEndTag) && --$this->syntaxEndLevel === 0) {
			$this->setSyntax($this->defaultSyntax);

		} elseif ($token->type === Token::MACRO_TAG && $token->name === 'contentType') {
			$this->setContentType($token->value);
		}
	}

}
