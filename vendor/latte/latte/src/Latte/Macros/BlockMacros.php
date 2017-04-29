<?php

/**
 * This file is part of the Latte (http://latte.nette.org)
 * Copyright (c) 2008 David Grudl (http://davidgrudl.com)
 */

namespace Latte\Macros;

use Latte;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\CompileException;


/**
 * Block macros.
 */
class BlockMacros extends MacroSet
{
	/** @var array */
	private $namedBlocks = array();

	/** @var bool */
	private $extends;


	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('include', array($me, 'macroInclude'));
		$me->addMacro('includeblock', array($me, 'macroIncludeBlock'));
		$me->addMacro('extends', array($me, 'macroExtends'));
		$me->addMacro('layout', array($me, 'macroExtends'));
		$me->addMacro('block', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('define', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('snippet', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('snippetArea', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('ifset', array($me, 'macroIfset'), '}');
		$me->addMacro('elseifset', array($me, 'macroIfset'), '}');
	}


	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{
		$this->namedBlocks = array();
		$this->extends = NULL;
	}


	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		// try close last block
		$last = $this->getCompiler()->getMacroNode();
		if ($last && $last->name === 'block') {
			$this->getCompiler()->closeMacro($last->name);
		}

		$epilog = $prolog = array();

		if ($this->namedBlocks) {
			foreach ($this->namedBlocks as $name => $code) {
				$func = '_lb' . substr(md5($this->getCompiler()->getTemplateId() . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
				$snippet = $name[0] === '_';
				$prolog[] = "//\n// block $name\n//\n"
					. "if (!function_exists(\$_b->blocks[" . var_export($name, TRUE) . "][] = '$func')) { "
					. "function $func(\$_b, \$_args) { foreach (\$_args as \$__k => \$__v) \$\$__k = \$__v"
					. ($snippet ? '; $_control->redrawControl(' . var_export((string) substr($name, 1), TRUE) . ', FALSE)' : '')
					. "\n?>$code<?php\n}}";
			}
			$prolog[] = "//\n// end of blocks\n//";
		}

		if ($this->namedBlocks || $this->extends) {
			$prolog[] = '// template extending';

			$prolog[] = '$_l->extends = '
				. ($this->extends ? $this->extends : 'empty($_g->extended) && isset($_control) && $_control instanceof Nette\Application\UI\Presenter ? $_control->findLayoutTemplateFile() : NULL')
				. '; $_g->extended = TRUE;';

			$prolog[] = 'if ($_l->extends) { ' . ($this->namedBlocks ? 'ob_start();' : 'return $template->renderChildTemplate($_l->extends, get_defined_vars());') . '}';
		}

		return array(implode("\n\n", $prolog), implode("\n", $epilog));
	}


	/********************* macros ****************d*g**/


	/**
	 * {include #block}
	 */
	public function macroInclude(MacroNode $node, PhpWriter $writer)
	{
		$destination = $node->tokenizer->fetchWord(); // destination [,] [params]
		if (!preg_match('~#|[\w-]+\z~A', $destination)) {
			return FALSE;
		}

		$destination = ltrim($destination, '#');
		$parent = $destination === 'parent';
		if ($destination === 'parent' || $destination === 'this') {
			for ($item = $node->parentNode; $item && $item->name !== 'block' && !isset($item->data->name); $item = $item->parentNode);
			if (!$item) {
				throw new CompileException("Cannot include $destination block outside of any block.");
			}
			$destination = $item->data->name;
		}

		$name = strpos($destination, '$') === FALSE ? var_export($destination, TRUE) : $destination;
		if (isset($this->namedBlocks[$destination]) && !$parent) {
			$cmd = "call_user_func(reset(\$_b->blocks[$name]), \$_b, %node.array? + get_defined_vars())";
		} else {
			$cmd = 'Latte\Macros\BlockMacrosRuntime::callBlock' . ($parent ? 'Parent' : '') . "(\$_b, $name, %node.array? + " . ($parent ? 'get_defined_vars' : '$template->getParameters') . '())';
		}

		if ($node->modifiers) {
			return $writer->write("ob_start(); $cmd; echo %modify(ob_get_clean())");
		} else {
			return $writer->write($cmd);
		}
	}


	/**
	 * {includeblock "file"}
	 */
	public function macroIncludeBlock(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('ob_start(); $_b->templates[%var]->renderChildTemplate(%node.word, %node.array? + get_defined_vars()); echo rtrim(ob_get_clean())',
			$this->getCompiler()->getTemplateId());
	}


	/**
	 * {extends auto | none | $var | "file"}
	 */
	public function macroExtends(MacroNode $node, PhpWriter $writer)
	{
		if (!$node->args) {
			throw new CompileException("Missing destination in {{$node->name}}");
		}
		if (!empty($node->parentNode)) {
			throw new CompileException("{{$node->name}} must be placed outside any macro.");
		}
		if ($this->extends !== NULL) {
			throw new CompileException("Multiple {{$node->name}} declarations are not allowed.");
		}
		if ($node->args === 'none') {
			$this->extends = 'FALSE';
		} elseif ($node->args === 'auto') {
			$this->extends = '$_presenter->findLayoutTemplateFile()';
		} else {
			$this->extends = $writer->write('%node.word%node.args');
		}
		return;
	}


	/**
	 * {block [[#]name]}
	 * {snippet [name [,]] [tag]}
	 * {snippetArea [name]}
	 * {define [#]name}
	 */
	public function macroBlock(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();

		if ($node->name === '#') {
			trigger_error('Shortcut {#block} is deprecated.', E_USER_DEPRECATED);

		} elseif ($node->name === 'block' && $name === FALSE) { // anonymous block
			return $node->modifiers === '' ? '' : 'ob_start()';
		}

		$node->data->name = $name = ltrim($name, '#');
		if ($name == NULL) {
			if ($node->name === 'define') {
				throw new CompileException('Missing block name.');
			}

		} elseif (strpos($name, '$') !== FALSE) { // dynamic block/snippet
			if ($node->name === 'snippet') {
				for ($parent = $node->parentNode; $parent && !($parent->name === 'snippet' || $parent->name === 'snippetArea'); $parent = $parent->parentNode);
				if (!$parent) {
					throw new CompileException('Dynamic snippets are allowed only inside static snippet/snippetArea.');
				}
				$parent->data->dynamic = TRUE;
				$node->data->leave = TRUE;
				$node->closingCode = "<?php \$_l->dynSnippets[\$_l->dynSnippetId] = ob_get_flush() ?>";

				if ($node->prefix) {
					$node->attrCode = $writer->write("<?php echo ' id=\"' . (\$_l->dynSnippetId = \$_control->getSnippetId({$writer->formatWord($name)})) . '\"' ?>");
					return $writer->write('ob_start()');
				}
				$tag = trim($node->tokenizer->fetchWord(), '<>');
				$tag = $tag ? $tag : 'div';
				$node->closingCode .= "\n</$tag>";
				return $writer->write("?>\n<$tag id=\"<?php echo \$_l->dynSnippetId = \$_control->getSnippetId({$writer->formatWord($name)}) ?>\"><?php ob_start()");

			} else {
				$node->data->leave = TRUE;
				$fname = $writer->formatWord($name);
				$node->closingCode = '<?php }} ' . ($node->name === 'define' ? '' : "call_user_func(reset(\$_b->blocks[$fname]), \$_b, get_defined_vars())") . ' ?>';
				$func = '_lb' . substr(md5($this->getCompiler()->getTemplateId() . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
				return "\n\n//\n// block $name\n//\n"
					. "if (!function_exists(\$_b->blocks[$fname]['{$this->getCompiler()->getTemplateId()}'] = '$func')) { "
					. "function $func(\$_b, \$_args) { foreach (\$_args as \$__k => \$__v) \$\$__k = \$__v";
			}
		}

		// static snippet/snippetArea
		if ($node->name === 'snippet' || $node->name === 'snippetArea') {
			if ($node->prefix && isset($node->htmlNode->attrs['id'])) {
				throw new CompileException('Cannot combine HTML attribute id with n:snippet.');
			}
			$node->data->name = $name = '_' . $name;
		}

		if (isset($this->namedBlocks[$name])) {
			throw new CompileException("Cannot redeclare static {$node->name} '$name'");
		}

		$prolog = $this->namedBlocks ? '' : "if (\$_l->extends) { ob_end_clean(); return \$template->renderChildTemplate(\$_l->extends, get_defined_vars()); }\n";
		$this->namedBlocks[$name] = TRUE;

		$include = 'call_user_func(reset($_b->blocks[%var]), $_b, ' . (($node->name === 'snippet' || $node->name === 'snippetArea') ? '$template->getParameters()' : 'get_defined_vars()') . ')';
		if ($node->modifiers) {
			$include = "ob_start(); $include; echo %modify(ob_get_clean())";
		}

		if ($node->name === 'snippet') {
			if ($node->prefix) {
				$node->attrCode = $writer->write('<?php echo \' id="\' . $_control->getSnippetId(%var) . \'"\' ?>', (string) substr($name, 1));
				return $writer->write($prolog . $include, $name);
			}
			$tag = trim($node->tokenizer->fetchWord(), '<>');
			$tag = $tag ? $tag : 'div';
			return $writer->write("$prolog ?>\n<$tag id=\"<?php echo \$_control->getSnippetId(%var) ?>\"><?php $include ?>\n</$tag><?php ",
				(string) substr($name, 1), $name
			);

		} elseif ($node->name === 'define') {
			return $prolog;

		} else { // block, snippetArea
			return $writer->write($prolog . $include, $name);
		}
	}


	/**
	 * {/block}
	 * {/snippet}
	 * {/snippetArea}
	 * {/define}
	 */
	public function macroBlockEnd(MacroNode $node, PhpWriter $writer)
	{
		if (isset($node->data->name)) { // block, snippet, define
			if ($node->name === 'snippet' && $node->prefix === MacroNode::PREFIX_NONE // n:snippet -> n:inner-snippet
				&& preg_match('#^.*? n:\w+>\n?#s', $node->content, $m1) && preg_match('#[ \t]*<[^<]+\z#s', $node->content, $m2)
			) {
				$node->openingCode = $m1[0] . $node->openingCode;
				$node->content = substr($node->content, strlen($m1[0]), -strlen($m2[0]));
				$node->closingCode .= $m2[0];
			}

			if (empty($node->data->leave)) {
				if ($node->name === 'snippetArea') {
					$node->content = "<?php \$_control->snippetMode = isset(\$_snippetMode) && \$_snippetMode; ?>{$node->content}<?php \$_control->snippetMode = FALSE; ?>";
				}
				if (!empty($node->data->dynamic)) {
					$node->content .= '<?php if (isset($_l->dynSnippets)) return $_l->dynSnippets; ?>';
				}
				if ($node->name === 'snippetArea') {
					$node->content .= '<?php return FALSE; ?>';
				}
				$this->namedBlocks[$node->data->name] = $tmp = rtrim(ltrim($node->content, "\n"), " \t");
				$node->content = substr_replace($node->content, $node->openingCode . "\n", strspn($node->content, "\n"), strlen($tmp));
				$node->openingCode = '<?php ?>';
			}

		} elseif ($node->modifiers) { // anonymous block with modifier
			return $writer->write('echo %modify(ob_get_clean())');
		}
	}


	/**
	 * {ifset #block}
	 * {elseifset #block}
	 */
	public function macroIfset(MacroNode $node, PhpWriter $writer)
	{
		if (!preg_match('~#|[\w-]+\z~A', $node->args)) {
			return FALSE;
		}
		$list = array();
		while (($name = $node->tokenizer->fetchWord()) !== FALSE) {
			$list[] = preg_match('~#|[\w-]+\z~A', $name)
				? '$_b->blocks["' . ltrim($name, '#') . '"]'
				: $writer->formatArgs(new Latte\MacroTokens($name));
		}
		return ($node->name === 'elseifset' ? '} else' : '')
			. 'if (isset(' . implode(', ', $list) . ')) {';
	}

}
