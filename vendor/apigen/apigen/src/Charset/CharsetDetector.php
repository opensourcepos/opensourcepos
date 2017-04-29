<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Charset;

use ApiGen\Charset\Configuration\CharsetOptionsResolver;


class CharsetDetector
{

	/**
	 * @var string[]
	 */
	private $charsets = [];

	/**
	 * @var array { filePath => fileEncoding }
	 */
	private $detectedFileEncodings = [];

	/**
	 * @var CharsetOptionsResolver
	 */
	private $charsetOptionsResolver;


	public function __construct(CharsetOptionsResolver $charsetOptionsResolver)
	{
		$this->charsetOptionsResolver = $charsetOptionsResolver;
		$this->charsets = [Encoding::UTF_8];
	}


	public function setCharsets(array $charsets)
	{
		$this->charsets = $this->charsetOptionsResolver->resolve($charsets);
	}


	/**
	 * @param string $filePath
	 * @return string
	 */
	public function detectForFilePath($filePath)
	{
		if (isset($this->detectedFileEncodings[$filePath])) {
			return $this->detectedFileEncodings[$filePath];
		}

		$detectedEncoding = $this->detectForContent(file_get_contents($filePath));
		$this->detectedFileEncodings[$filePath] = $detectedEncoding;
		return $detectedEncoding;
	}


	/**
	 * @param string $fileContent
	 * @return string
	 */
	private function detectForContent($fileContent)
	{
		$fileEncoding = mb_detect_encoding($fileContent, $this->charsets);

		// mb_detect_encoding can not handle WINDOWS-1250 and returns ISO-8859-1 instead
		if ($this->isWindows1250($fileEncoding, $fileContent)) {
			return Encoding::WIN_1250;
		}

		return $fileEncoding;
	}


	/**
	 * @param string $fileEncoding
	 * @param string $fileContent
	 * @return bool
	 */
	private function isWindows1250($fileEncoding, $fileContent)
	{
		if ($fileEncoding === Encoding::ISO_8859_1 && preg_match('~[\x7F-\x9F\xBC]~', $fileContent)) {
			return TRUE;
		}
		return FALSE;
	}

}
