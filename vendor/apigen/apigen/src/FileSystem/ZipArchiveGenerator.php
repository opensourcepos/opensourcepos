<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\FileSystem;

use Nette\Utils\Finder;
use Nette\Utils\Strings;
use RuntimeException;
use SplFileInfo;
use ZipArchive;


class ZipArchiveGenerator
{

	/**
	 * @param string $source
	 * @param string $zipFile
	 */
	public function zipDirToFile($source, $zipFile)
	{
		if ( ! extension_loaded('zip')) {
			throw new RuntimeException('Extension zip is not loaded');
		}

		$archive = new ZipArchive;
		$archive->open($zipFile, ZipArchive::CREATE);

		$directory = basename($zipFile, '.zip');

		/** @var SplFileInfo $file */
		foreach (Finder::findFiles('*')->from($source) as $file) {
			$relativePath = Strings::substring($file->getRealPath(), strlen($source) + 1);
			$archive->addFile($file, $directory . '/' . $relativePath);
		}

		$archive->close();
	}

}
