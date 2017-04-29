<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Http;

use Nette;


/**
 * Provides access to individual files that have been uploaded by a client.
 *
 * @property-read string $name
 * @property-read string $sanitizedName
 * @property-read string|NULL $contentType
 * @property-read int $size
 * @property-read string $temporaryFile
 * @property-read int $error
 * @property-read bool $ok
 * @property-read string|NULL $contents
 */
class FileUpload
{
	use Nette\SmartObject;

	/** @var string */
	private $name;

	/** @var string */
	private $type;

	/** @var string */
	private $size;

	/** @var string */
	private $tmpName;

	/** @var int */
	private $error;


	public function __construct($value)
	{
		foreach (['name', 'type', 'size', 'tmp_name', 'error'] as $key) {
			if (!isset($value[$key]) || !is_scalar($value[$key])) {
				$this->error = UPLOAD_ERR_NO_FILE;
				return; // or throw exception?
			}
		}
		$this->name = $value['name'];
		$this->size = $value['size'];
		$this->tmpName = $value['tmp_name'];
		$this->error = $value['error'];
	}


	/**
	 * Returns the file name.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Returns the sanitized file name.
	 * @return string
	 */
	public function getSanitizedName()
	{
		return trim(Nette\Utils\Strings::webalize($this->name, '.', FALSE), '.-');
	}


	/**
	 * Returns the MIME content type of an uploaded file.
	 * @return string|NULL
	 */
	public function getContentType()
	{
		if ($this->isOk() && $this->type === NULL) {
			$this->type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->tmpName);
		}
		return $this->type;
	}


	/**
	 * Returns the size of an uploaded file.
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}


	/**
	 * Returns the path to an uploaded file.
	 * @return string
	 */
	public function getTemporaryFile()
	{
		return $this->tmpName;
	}


	/**
	 * Returns the path to an uploaded file.
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->tmpName;
	}


	/**
	 * Returns the error code. {@link http://php.net/manual/en/features.file-upload.errors.php}
	 * @return int
	 */
	public function getError()
	{
		return $this->error;
	}


	/**
	 * Is there any error?
	 * @return bool
	 */
	public function isOk()
	{
		return $this->error === UPLOAD_ERR_OK;
	}


	/**
	 * @return bool
	 */
	public function hasFile()
	{
		return $this->error !== UPLOAD_ERR_NO_FILE;
	}


	/**
	 * Move uploaded file to new location.
	 * @param  string
	 * @return static
	 */
	public function move($dest)
	{
		$dir = dirname($dest);
		@mkdir($dir, 0777, TRUE); // @ - dir may already exist
		if (!is_dir($dir)) {
			throw new Nette\InvalidStateException("Directory '$dir' cannot be created. " . error_get_last()['message']);
		}
		@unlink($dest); // @ - file may not exists
		Nette\Utils\Callback::invokeSafe(
			is_uploaded_file($this->tmpName) ? 'move_uploaded_file' : 'rename',
			[$this->tmpName, $dest],
			function ($message) use ($dest) {
				throw new Nette\InvalidStateException("Unable to move uploaded file '$this->tmpName' to '$dest'. $message");
			}
		);
		@chmod($dest, 0666); // @ - possible low permission to chmod
		$this->tmpName = $dest;
		return $this;
	}


	/**
	 * Is uploaded file GIF, PNG or JPEG?
	 * @return bool
	 */
	public function isImage()
	{
		return in_array($this->getContentType(), ['image/gif', 'image/png', 'image/jpeg'], TRUE);
	}


	/**
	 * Returns the image.
	 * @return Nette\Utils\Image
	 * @throws Nette\Utils\ImageException
	 */
	public function toImage()
	{
		return Nette\Utils\Image::fromFile($this->tmpName);
	}


	/**
	 * Returns the dimensions of an uploaded image as array.
	 * @return array|NULL
	 */
	public function getImageSize()
	{
		return $this->isOk() ? @getimagesize($this->tmpName) : NULL; // @ - files smaller than 12 bytes causes read error
	}


	/**
	 * Get file contents.
	 * @return string|NULL
	 */
	public function getContents()
	{
		// future implementation can try to work around safe_mode and open_basedir limitations
		return $this->isOk() ? file_get_contents($this->tmpName) : NULL;
	}

}
