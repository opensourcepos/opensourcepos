<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
	'@PSR12' => true,
	'indentation_type' => true,
	'braces' => ['indent_with_space' => false],
])
	->setFinder($finder)
	->setIndent("\t")
	->setLineEnding("\n");
