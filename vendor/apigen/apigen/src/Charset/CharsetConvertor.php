<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\Charset;


class CharsetConvertor
{

	const ICONV_UTF_CHARSET = 'UTF-8//TRANSLIT//IGNORE';

	/**
	 * @var CharsetDetector
	 */
	private $charsetDetector;


	public function __construct(CharsetDetector $charsetDetector)
	{
		$this->charsetDetector = $charsetDetector;
	}


	/**
	 * @param string $filePath
	 * @return string
	 */
	public function convertFileToUtf($filePath)
	{
		$fileEncoding = $this->charsetDetector->detectForFilePath($filePath);
		$content = file_get_contents($filePath);

		if ($fileEncoding === Encoding::UTF_8) {
			return $content;

		} else {
			return $this->convertContentToUtf($content, $fileEncoding);
		}
	}


	/**
	 * @param string $content
	 * @param string $fileEncoding
	 * @return string
	 */
	private function convertContentToUtf($content, $fileEncoding)
	{
		return @iconv($fileEncoding, self::ICONV_UTF_CHARSET, $content);
	}

}
