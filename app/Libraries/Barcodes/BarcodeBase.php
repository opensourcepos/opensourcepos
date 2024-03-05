<?php
/**
 *
 * @package     Barcode Creator
 * @copyright   (c) 2011 emberlabs.org
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/samt/barcode
 *
 * Minimum Requirement: PHP 5.3.0
 */

namespace App\Libraries\Barcodes;

/**
 * emberlabs Barcode Creator - Barcode Base
 * 	     Abstract Base
 *
 *
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/samt/barcode
 */
abstract class BarcodeBase
{
	/*
	 * GD Resource
	 * @var resource
	 */
	protected $img = null;

	/*
	 * @var data - to be set
	 */
	protected $data = '';

	/*
	 * @var int x (width)
	 */
	protected $x = 0;

	/*
	 * @var int y (height)
	 */
	protected $y = 0;

	/*
	 * Print Human Text?
	 * @var bool
	 */
	protected $humanText = true;

	/*
	 * Quality
	 * @var int
	 */
	protected $jpgQuality = 85;

	/**
	 * (Abstract) Set the data
	 *
	 * @param $data - (int or string) Data to be encoded
	 * @return void
	 * @throws OverflowException
	 */
	abstract public function setData($data): void;

	/**
	 * Get the data
	 *
	 * @return BarcodeInterface
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Validate the given barcode.
	 *
	 * @param $barcode The barcode to validate
	 * @return bool true if it complies with the barcode formatting
	 */
	public function validate($barcode)
	{
		return true;
	}

	/**
	 * Generate a barcode for this implementation using the given seed.
	 * Default implementation returns just the seed
	 * @param $number The seed to generate a barcode for
	 * @return string|null The generated barcode
	 */
	public function generate($number)
	{
		return $number;
	}

	/**
	 * (Abstract) Draw the image
	 *
	 * @return void
	 */
	abstract public function draw();

	/**
	 * Set the Dimensions
	 *
	 * @param int $x
	 * @param int $y
	 * @return BarcodeBase
	 */
	public function setDimensions($x, $y)
	{
		$this->x = (int) $x;
		$this->y = (int) $y;

		return $this;
	}

	/**
	 * Set Quality
	 *
	 * @param int $q - jpeg quality
	 * @return BarcodeBase
	 */
	public function setQuality($q)
	{
		$this->jpgQuality = (int) $q;

		return $this;
	}

	/**
	 * Display human readable text below the code
	 *
	 * @param boolean $enable - Enable the human readable text
	 * @return BarcodeBase
	 */
	public function enableHumanText($enable = true)
	{
		$this->humanText = (boolean) $enable;

		return $this;
	}

	/**
	 * Output Image to the buffer
	 *
	 * @param $type
	 * @return void
	 */
	public function output($type = 'png'): void
	{
		switch($type)
		{
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->img, null, $this->jpgQuality);
			break;

			case 'gif':
				imagegif($this->img);
			break;

			case 'png':
			default:
				imagepng($this->img);
			break;
		}
	}

	/**
	 * Save Image
	 *
	 * @param string $filename - File to write to (needs to have .png, .gif, or .jpg extension)
	 * @return void
	 * @throws RuntimeException - If the file could not be written or some other I/O error.
	 */
	public function save($filename): void
	{
		$type = strtolower(substr(strrchr($filename, '.'), 1));

		switch($type)
		{
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->img, $filename, $this->jpgQuality);
			break;

			case 'gif':
				imagegif($this->img, $filename);
			break;

			case 'png':
				imagepng($this->img, $filename);
			break;

			default:
				throw new \RuntimeException("Could not determine file type.");
		}
	}

	/**
	 * Base64 Encoded
	 * For ouput in-page
	 *
	 * @return string
	 */
	public function base64(): string
	{
		ob_start();
		$this->output();

		return base64_encode(ob_get_clean());
	}
}
?>
