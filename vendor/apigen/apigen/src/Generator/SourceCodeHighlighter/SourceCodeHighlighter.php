<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Generator\SourceCodeHighlighter;


interface SourceCodeHighlighter
{

	/**
	 * Highlights passed code
	 *
	 * @param string $sourceCode
	 * @return string
	 */
	function highlight($sourceCode);


	/**
	 * Highlights passed code an adds line number at the beginning.
	 *
	 * @param string $sourceCode
	 * @return string
	 */
	function highlightAndAddLineNumbers($sourceCode);

}
