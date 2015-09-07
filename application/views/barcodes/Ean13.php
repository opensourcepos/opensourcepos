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

namespace emberlabs\Barcode;

/**
 * emberlabs Barcode Creator - Ean13
 * 	     Generate Ean13 Barcodes
 *
 *
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/samt/barcode
 */
class Ean13 extends BarcodeBase
{
	/*
	 * @var data - to be set
	 */
	private $data = '';


	/*
	 * Set the data
	 *
	 * @param mixed data - (int or string) Data to be encoded
	 * @return instance of \emberlabs\Barcode\BarcodeInterface
	 * @return throws \OverflowException
	 */
	public function setData($data)
	{
		$this->data = $data;
	}

	/*
	 * Draw the image
	 *
	 * @return void
	 */
	public function draw()
	{
		$this->img = @imagecreate($this->x, $this->y);
		$white = imagecolorallocate($this->img, 255, 255, 255);
		$black = imagecolorallocate($this->img, 0, 0, 0);

/*
		// Print the code
		foreach($charAry as $k => $char)
		{
			$code = $this->getBar($char);
			$checkSumCollector += $this->getKey($char) * $k; // $k will be 0 for our first

			foreach(str_split((string) $code) as $bit)
			{
				imagefilledrectangle($this->img, $currentX, 0, ($currentX + $pxPerBar), ($this->y - 1), (($bit == '1') ? $black : $white));
				$currentX += $pxPerBar;
			}
		}

		$ending[] = self::$barMap[$checkSumCollector % 103];
		$ending[] = self::$barMap[106]; // STOP.

		foreach($ending as $code)
		{
			foreach(str_split((string) $code) as $bit)
			{
				imagefilledrectangle($this->img, $currentX, 0, ($currentX + $pxPerBar), ($this->y - 1), (($bit == '1') ? $black : $white));
				$currentX += $pxPerBar;
			}
		}
*/
	}
}
?>