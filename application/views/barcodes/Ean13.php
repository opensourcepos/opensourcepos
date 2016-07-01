<?php
/**
 *
 * @package   Barcode Creator
 * @copyright (c) 2011 emberlabs.org
 * @license   http://opensource.org/licenses/mit-license.php The MIT License
 * @link      https://github.com/samt/barcode
 *
 * Minimum Requirement: PHP 5.3.0
 */
 
/**
 * Image_Barcode2_Driver_Ean13 class
 *
 * Renders EAN 13 barcodes
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Image
 * @package   Image_Barcode2
 * @author    Didier Fournout <didier.fournout@nyc.fr>
 * @copyright 2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://pear.php.net/package/Image_Barcode2
 */

namespace emberlabs\Barcode;

/**
 * emberlabs Barcode Creator - Ean13
 *           Generate Ean13 Barcodes
 *
 *
 * @license	 http://opensource.org/licenses/mit-license.php The MIT License
 * @link		https://github.com/samt/barcode
 */
class Ean13 extends BarcodeBase
{
	/*
	 * Coding map
	 * @var array 
	 */
	private $_codingmap = array(
		'0' => array(
			'A' => array(0,0,0,1,1,0,1),
			'B' => array(0,1,0,0,1,1,1),
			'C' => array(1,1,1,0,0,1,0)
		),
		'1' => array(
			'A' => array(0,0,1,1,0,0,1),
			'B' => array(0,1,1,0,0,1,1),
			'C' => array(1,1,0,0,1,1,0)
		),
		'2' => array(
			'A' => array(0,0,1,0,0,1,1),
			'B' => array(0,0,1,1,0,1,1),
			'C' => array(1,1,0,1,1,0,0)
		),
		'3' => array(
			'A' => array(0,1,1,1,1,0,1),
			'B' => array(0,1,0,0,0,0,1),
			'C' => array(1,0,0,0,0,1,0)
		),
		'4' => array(
			'A' => array(0,1,0,0,0,1,1),
			'B' => array(0,0,1,1,1,0,1),
			'C' => array(1,0,1,1,1,0,0)
		),
		'5' => array(
			'A' => array(0,1,1,0,0,0,1),
			'B' => array(0,1,1,1,0,0,1),
			'C' => array(1,0,0,1,1,1,0)
		),
		'6' => array(
			'A' => array(0,1,0,1,1,1,1),
			'B' => array(0,0,0,0,1,0,1),
			'C' => array(1,0,1,0,0,0,0)
		),
		'7' => array(
			'A' => array(0,1,1,1,0,1,1),
			'B' => array(0,0,1,0,0,0,1),
			'C' => array(1,0,0,0,1,0,0)
		),
		'8' => array(
			'A' => array(0,1,1,0,1,1,1),
			'B' => array(0,0,0,1,0,0,1),
			'C' => array(1,0,0,1,0,0,0)
		),
		'9' => array(
			'A' => array(0,0,0,1,0,1,1),
			'B' => array(0,0,1,0,1,1,1),
			'C' => array(1,1,1,0,1,0,0)
		)
	);

	/*
	 * Coding map left
	 * @var array 
	 */
	private $_codingmapleft = array(
		'0' => array('A','A','A','A','A','A'),
		'1' => array('A','A','B','A','B','B'),
		'2' => array('A','A','B','B','A','B'),
		'3' => array('A','A','B','B','B','A'),
		'4' => array('A','B','A','A','B','B'),
		'5' => array('A','B','B','A','A','B'),
		'6' => array('A','B','B','B','A','A'),
		'7' => array('A','B','A','B','A','B'),
		'8' => array('A','B','A','B','B','A'),
		'9' => array('A','B','B','A','B','A')
	);

	/*
	 * Set the data
	 *
	 * @param mixed data - (int or string) Data to be encoded
	 * @return instance of \emberlabs\Barcode\BarcodeInterface
	 */
	public function setData($data)
	{
		$this->data = $data;
	}

	/*
	 * Generate EAN13 code out of a provided number
	 * Code taken from http://stackoverflow.com/questions/19890144/generate-valid-ean13-in-php (unknown copyright / license claims)
	 *
	 * @param number is the internal code you want to have EANed. The prefix, zero-padding and checksum are added by the function.
	 * @return string with complete EAN13 code
	 */
	public function generate($number)
	{
		$number = '200' . str_pad($number, 9, '0');
		$weightflag = true;
		$sum = 0;

		// Weight for a digit in the checksum is 3, 1, 3.. starting from the last digit.
		// loop backwards to make the loop length-agnostic. The same basic functionality
		// will work for codes of different lengths.
		for ($i = strlen($number) - 1; $i >= 0; --$i)
		{
			$sum += (int)$number[$i] * ($weightflag?3:1);
			$weightflag = !$weightflag;
		}
		$number .= (10 - ($sum % 10)) % 10;

		return $number;
	}

	public function validate($barcode)
	{
		// check to see if barcode is 13 digits long
		if (!preg_match("/^[0-9]{13}$/", $barcode)) {
			return false;
		}

		$digits = $barcode;

		// 1. Add the values of the digits in the
		// even-numbered positions: 2, 4, 6, etc.
		$even_sum = $digits[1] + $digits[3] + $digits[5] +
			$digits[7] + $digits[9] + $digits[11];

		// 2. Multiply this result by 3.
		$even_sum_three = $even_sum * 3;

		// 3. Add the values of the digits in the
		// odd-numbered positions: 1, 3, 5, etc.
		$odd_sum = $digits[0] + $digits[2] + $digits[4] +
			$digits[6] + $digits[8] + $digits[10];

		// 4. Sum the results of steps 2 and 3.
		$total_sum = $even_sum_three + $odd_sum;

		// 5. The check character is the smallest number which,
		// when added to the result in step 4, produces a multiple of 10.
		$next_ten = (ceil($total_sum / 10)) * 10;
		$check_digit = $next_ten - $total_sum;

		// if the check digit and the last digit of the
		// barcode are OK return true;
		if ($check_digit == $digits[12]) {
			return true;
		}

		return false;
	}

	/*
	 * Draw the image
	 *
	 * @return void
	 */
	public function draw()
	{
		// Bars is in reference to a single, 1-level bar
		$pxPerBar = 2;
		
		// Calculate the barcode width
		$barcodewidth = (strlen($this->data)) * (7 * $pxPerBar) 
			+ 3 * $pxPerBar  // left
			+ 5 * $pxPerBar  // center
			+ 3 * $pxPerBar  // right
			;

		$this->x = ($this->x == 0) ? $barcodewidth : $this->x;
			
		$this->img = @imagecreate($this->x, $this->y);
		
		if (!$this->img)
		{
			throw new \RuntimeException("Ean13: Image failed to initialize");
		}
		
		$white = imagecolorallocate($this->img, 255, 255, 255);
		$black = imagecolorallocate($this->img, 0, 0, 0);
		
		// Fill image with white color
		imagefill($this->img, 0, 0, $white);

		// get the first digit which is the key for creating the first 6 bars
		$key = substr($this->data, 0, 1);

		// Initiate x position centering the bar
		$xpos = ($this->x - $barcodewidth) / 2;
 
		// Draws the left guard pattern (bar-space-bar)
		// bar
		imagefilledrectangle(
			$this->img,
			$xpos,
			0,
			$xpos + $pxPerBar - 1,
			$this->y, 
			$black
		);

		$xpos += $pxPerBar;

		// space
		$xpos += $pxPerBar;

		// bar
		imagefilledrectangle(
			$this->img,
			$xpos,
			0,
			$xpos + $pxPerBar - 1,
			$this->y,
			$black
		);

		$xpos += $pxPerBar;

		// Draw left $this->data contents
		$set_array = $this->_codingmapleft[$key];

		for ($idx = 1; $idx < 7; ++$idx)
		{
			$value = substr($this->data, $idx, 1);

			foreach ($this->_codingmap[$value][$set_array[$idx - 1]] as $bar)
			{
				if ($bar)
				{
					imagefilledrectangle(
						$this->img,
						$xpos,
						0,
						$xpos + $pxPerBar - 1,
						$this->y,
						$black
					);
				}

				$xpos += $pxPerBar;
			}
		}

		// Draws the center pattern (space-bar-space-bar-space)
		// space
		$xpos += $pxPerBar;

		// bar
		imagefilledrectangle(
			$this->img,
			$xpos,
			0,
			$xpos + $pxPerBar - 1,
			$this->y,
			$black
		);

		$xpos += $pxPerBar;

		// space
		$xpos += $pxPerBar;

		// bar
		imagefilledrectangle(
			$this->img,
			$xpos,
			0,
			$xpos + $pxPerBar - 1,
			$this->y,
			$black
		);

		$xpos += $pxPerBar;

		// space
		$xpos += $pxPerBar;

		// Draw right $this->data contents
		for ($idx = 7; $idx < 13; ++$idx)
		{
			$value = substr($this->data, $idx, 1);

			foreach ($this->_codingmap[$value]['C'] as $bar)
			{
				if ($bar)
				{
					imagefilledrectangle(
						$this->img,
						$xpos,
						0,
						$xpos + $pxPerBar - 1,
						$this->y,
						$black
					);
				}

				$xpos += $pxPerBar;
			}
		}

		// Draws the right guard pattern (bar-space-bar)
		// bar
		imagefilledrectangle(
			$this->img,
			$xpos,
			0,
			$xpos + $pxPerBar - 1,
			$this->y,
			$black
		);

		$xpos += $pxPerBar;

		// space
		$xpos += $pxPerBar;

		// bar
		imagefilledrectangle(
			$this->img,
			$xpos,
			0,
			$xpos + $pxPerBar - 1,
			$this->y,
			$black
		);
	}
}
?>