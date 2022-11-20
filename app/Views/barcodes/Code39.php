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
 * emberlabs Barcode Creator - Code39
 * 	     Generate Code39 Barcodes
 *
 *
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/samt/barcode
 */
class Code39 extends BarcodeBase
{
	/*
	 * Binary map
	 * @var array binMap
	 */
	private static $binMap = array(
		' '	=> '011000100',
		'$'	=> '010101000',
		'%'	=> '000101010',
		'*'	=> '010010100', // Start/stop marker
		'+'	=> '010001010',
		'|'	=> '010000101',
		'.'	=> '110000100',
		'/'	=> '010100010',
		'-'	=> '010000101',
		'0'	=> '000110100',
		'1'	=> '100100001',
		'2'	=> '001100001',
		'3'	=> '101100000',
		'4'	=> '000110001',
		'5'	=> '100110000',
		'6'	=> '001110000',
		'7'	=> '000100101',
		'8'	=> '100100100',
		'9'	=> '001100100',
		'A'	=> '100001001',
		'B'	=> '001001001',
		'C'	=> '101001000',
		'D'	=> '000011001',
		'E'	=> '100011000',
		'F'	=> '001011000',
		'G'	=> '000001101',
		'H'	=> '100001100',
		'I'	=> '001001100',
		'J'	=> '000011100',
		'K'	=> '100000011',
		'L'	=> '001000011',
		'M'	=> '101000010',
		'N'	=> '000010011',
		'O'	=> '100010010',
		'P'	=> '001010010',
		'Q'	=> '000000111',
		'R'	=> '100000110',
		'S'	=> '001000110',
		'T'	=> '000010110',
		'U'	=> '110000001',
		'V'	=> '011000001',
		'W'	=> '111000000',
		'X'	=> '010010001',
		'Y'	=> '110010000',
		'Z'	=> '011010000',
	);

	/*
	 * const bar proportions
	 */
	const NARROW_BAR	= 20;
	const WIDE_BAR		= 55;
	const QUIET_BAR		= 35;

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
	 * Get a binary map value
	 */
	private function getMap($char)
	{
		return self::$binMap[$char] ?: self::$this->binMap[' '];
	}

	/*
	 * Draw the image
	 *
	 * Based on the implentation PHP Barcode Image Generator v1.0 
	 * by Charles J. Scheffold - cs@sid6581.net
	 * It was released into the Public Domain by its creator.
	 *
	 * @return void
	 */
	public function draw()
	{
		// I know, lots of junk.
		$data = '*' . strtoupper(ltrim(rtrim(trim($this->data), '*'), '*')) . '*';
	
		//                Length of data  X   [ 6 narrow bars       +     3 wide bars      + A single Quiet stop ] - a single quiet stop
		$pxPerChar = (strlen($data) * ((6 * self::NARROW_BAR) + (3 * self::WIDE_BAR) + self::QUIET_BAR)) - self::QUIET_BAR;
		$widthQuotient = $this->x / $pxPerChar;
		
		// Lengths per type
		$narrowBar	= (int) (self::NARROW_BAR * $widthQuotient);
		$wideBar	= (int) (self::WIDE_BAR * $widthQuotient);
		$quietBar	= (int) (self::QUIET_BAR * $widthQuotient);

		$imageWidth = (strlen($data) * ((6 * $narrowBar) + (3 * $wideBar) + $quietBar)) - $quietBar;

		// Do we have degenerate rectangles?
		if ($narrowBar < 1 || $wideBar < 1 || $quietBar < 1 || $narrowBar == $quietBar || $narrowBar == $wideBar || $wideBar == $quietBar)
		{
			throw new \OverflowException("You need to specify a bigger width to properly display this barcode");
		}

		$currentBarX = (int)(($this->x - $imageWidth) / 2);
		$charAry = str_split($data);

		$this->img = @imagecreate($this->x, $this->y);

		if (!$this->img)
		{
			throw new \RuntimeException("Code39: Image failed to initialize");
		}
		
		// Grab our colors
		$white = imagecolorallocate($this->img, 255, 255, 255);
		$black = imagecolorallocate($this->img, 0, 0, 0);
		$color = $black;

		foreach($charAry as $_k => $char)
		{
			$code = str_split($this->getMap($char));
			$color = $black; 

			foreach($code as $k => $bit)
			{
				// Narrow bar
				if ($bit == '0')
				{
					imagefilledrectangle($this->img, $currentBarX, 0, ($currentBarX + $narrowBar), ($this->y - 1), $color);
					$currentBarX += $narrowBar;
				}
				// Wide Bar
				elseif($bit == '1')
				{
					imagefilledrectangle($this->img, $currentBarX, 0, ($currentBarX + $wideBar), ($this->y - 1), $color);
					$currentBarX += $wideBar;
				}

				$color = ($color == $black) ? $white : $black;
			}

			// Skip the spacer on the last run 
			if ($_k == (sizeof($charAry) - 1))
			{
				break;
			}

			// Draw spacer
			imagefilledrectangle($this->img, $currentBarX, 0, ($currentBarX + $quietBar), ($this->y - 1), $white);
			$currentBarX += $quietBar;
		}
	}
}
?>