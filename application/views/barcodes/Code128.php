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
 * emberlabs Barcode Creator - Code128
 * 	     Generate Code128 Barcodes
 *
 *
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/samt/barcode
 */
class Code128 extends BarcodeBase
{
	/*
	 * Sub Type encoding
	 * @var int (should be a class constant)
	 */
	private $type = self::TYPE_AUTO;

	/*
	 * This map maps the bar code to the common index. We use the built-in 
	 * index that PHP gives us to produce the common index. 
	 * @var static array 
	 */
	private static $barMap = array(
		11011001100, 11001101100, 11001100110, 10010011000, 10010001100, // 4 (end)
		10001001100, 10011001000, 10011000100, 10001100100, 11001001000, // 9
		11001000100, 11000100100, 10110011100, 10011011100, 10011001110, // 14
		10111001100, 10011101100, 10011100110, 11001110010, 11001011100, // 19
		11001001110, 11011100100, 11001110100, 11101101110, 11101001100, // 24
		11100101100, 11100100110, 11101100100, 11100110100, 11100110010, // 29
		11011011000, 11011000110, 11000110110, 10100011000,	10001011000, // 34
		10001000110, 10110001000, 10001101000, 10001100010,	11010001000, // 39
		11000101000, 11000100010, 10110111000, 10110001110, 10001101110, // 44
		10111011000, 10111000110, 10001110110, 11101110110, 11010001110, // 49
		11000101110, 11011101000, 11011100010, 11011101110, 11101011000, // 54
		11101000110, 11100010110, 11101101000, 11101100010, 11100011010, // 59
		11101111010, 11001000010, 11110001010, 10100110000, 10100001100, // 64
		10010110000, 10010000110, 10000101100, 10000100110, 10110010000, // 69
		10110000100, 10011010000, 10011000010, 10000110100, 10000110010, // 74
		11000010010, 11001010000, 11110111010, 11000010100, 10001111010, // 79
		10100111100, 10010111100, 10010011110, 10111100100, 10011110100, // 84
		10011110010, 11110100100, 11110010100, 11110010010, 11011011110, // 89
		11011110110, 11110110110, 10101111000, 10100011110, 10001011110, // 94
		10111101000, 10111100010, 11110101000, 11110100010, 10111011110, // 99
		10111101110, 11101011110, 11110101110, 11010000100, 11010010000, // 104
		11010011100, 1100011101011 // 106 (last char, also one bit longer)
	);

	/*
	 * This map takes the charset from subtype A and PHP will index the array
	 * natively to the matching code from the barMap.
	 * @var static array 
	 */
	private static $mapA = array(
		' ', '!', '"', '#', '$', '%', '&', "'", '(', ')', // 9 (end)
		'*', '+', ',', '-', '.', '/', '0', '1', '2', '3', // 19
		'4', '5', '6', '7', '8', '9', ':', ';', '<', '=', // 29
		'>', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', // 39
		'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', // 49
		'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', // 59
		'\\', ']', '^', '_', // 63 (We're going into the weird bytes next)
		
		// Hex is a little more concise in this context
		"\x00", "\x01", "\x02", "\x03", "\x04", "\x05", // 69
		"\x06", "\x07", "\x08", "\x09", "\x0A", "\x0B", // 75
		"\x0C", "\x0D", "\x0E", "\x0F", "\x10", "\x11", // 81
		"\x12", "\x13", "\x14", "\x15", "\x16", "\x17", // 87
		"\x18", "\x19", "\x1A", "\x1B", "\x1C", "\x1D", // 93
		"\x1E", "\x1F", // 95
		
		// Now for system codes
		'FNC_3', 'FNC_2', 'SHIFT_B', 'CODE_C', 'CODE_B', // 100
		'FNC_4', 'FNC_1', 'START_A', 'START_B', 'START_C', // 105
		'STOP',	// 106
	);

	/*
	 * Same idea from MapA applied here to B.
	 * @var static array
	 */
	private static $mapB = array(
		' ', '!', '"', '#', '$', '%', '&', "'", '(', ')', // 9 (end)
		'*', '+', ',', '-', '.', '/', '0', '1', '2', '3', // 19
		'4', '5', '6', '7', '8', '9', ':', ';', '<', '=', // 29
		'>', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', // 39
		'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', // 49
		'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', // 59
		'\\', ']', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', // 69
		'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', // 79
		'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', // 89
		'z', '{', '|', '}', '~', "\x7F", // 95
		
		// Now for system codes
		'FNC_3', 'FNC_2', 'SHIFT_A', 'CODE_C', 'FNC_4', // 100
		'CODE_A', 'FNC_1', 'START_A', 'START_B', 'START_C', // 105
		'STOP',	// 106
	);

	/*
	 * Map C works a little different. The index is the value when the mapping
	 * occors. 
	 * @var static array
	 */
	private static $mapC = array(
		100 => 
		'CODE_B', 'CODE_A', 'FNC_1', 'START_A', 'START_B', 
		'START_C', 'STOP', // 106
	);

	/*
	 * Subtypes
	 */
	const TYPE_AUTO	= 0; // Automatically detect the best code
	const TYPE_A	= 1; // ASCII 00-95 (0-9, A-Z, Control codes, and some special chars)
	const TYPE_B	= 2; // ASCII 32-127 (0-9, A-Z, a-z, special chars)
	const TYPE_C	= 3; // Numbers 00-99 (two digits per code)

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
	 * Set the subtype
	 * Defaults to Autodetect
	 * @param int type - Const flag for the type
	 */
	public function setSubType($type)
	{
		$this->type = ($type < 1 || $type > 3) ? self::TYPE_AUTO : (int) $type;
	}

	/*
	 * Get they key (value of the character)
	 * @return int - pattern
	 */
	private function getKey($char)
	{
		switch ($this->type)
		{
			case self::TYPE_A:
				return array_search($char, self::$mapA);
			break;

			case self::TYPE_B:
				return array_search($char, self::$mapB);			
			break;

			case self::TYPE_C:
				$charInt = (int) $char;
				if (strlen($char) == 2 && $charInt <= 99 && $charInt >= 0)
				{
					return $charInt;
				}

				return array_search($char, self::$mapC);
			break;
	
			default:
				$this->resolveSubtype();
				return $this->getKey($char); // recursion!
			break;
		}
	}

	/*
	 * Get the bar
	 * @return int - pattern
	 */
	private function getBar($char)
	{
		$key = $this->getKey($char);

		return self::$barMap[($key !== false) ? $key : 0];
	}

	/*
	 * Resolve subtype 
	 * @todo - Do some better charset checking and enforcement
	 * @return void
	 */
	private function resolveSubtype()
	{
		if ($this->type == self::TYPE_AUTO)
		{
			// If it is purely numeric, this is easy
			if (is_numeric($this->data))
			{
				$this->type = self::TYPE_C;
			}
			// Are there only capitals?
			elseif(strtoupper($this->data) == $this->data)
			{
				$this->type = self::TYPE_A;
			}
			else
			{
				$this->type = self::TYPE_B;
			}
		}
	}

	/*
	 * Get the name of a start char fr te current subtype
	 * @return string
	 */
	private function getStartChar()
	{
		$this->resolveSubtype();

		switch($this->type)
		{
			case self::TYPE_A: return 'START_A'; break;
			case self::TYPE_B: return 'START_B'; break;
			case self::TYPE_C: return 'START_C'; break;
		}
	}

	/*
	 * Draw the image
	 *
	 * @return void
	 */
	public function draw()
	{
		$this->resolveSubtype();
		$charAry = str_split($this->data);

		// Calc scaling
		// Bars is in reference to a single, 1-level bar
		$numBarsRequired = ($this->type != self::TYPE_C) ? (sizeof($charAry) * 11) + 35 : ((sizeof($charAry)/2) * 11) + 35;
		$this->x  = ($this->x == 0) ? $numBarsRequired : $this->x;
		$pxPerBar = (int) ($this->x / $numBarsRequired);
		$currentX = ($this->x - ($numBarsRequired  * $pxPerBar)) / 2;

		if ($pxPerBar < 1)
		{
			throw new \LogicException("Not enough space on this barcode for this message, increase the width of the barcode");
		}

		if ($this->type == self::TYPE_C)
		{
			if (sizeof($charAry) % 2)
			{
				array_unshift($charAry, '0');
			}

			$pairs = '';
			$newAry = array();
			foreach($charAry as $k => $char)
			{
				if (($k % 2) == 0 && $k != 0)
				{
					$newAry[] = $pairs;
					$pairs = '';
				}

				$pairs .= $char;
			}

			$newAry[] = $pairs;
			$charAry = $newAry;
		}

		// Add the start
		array_unshift($charAry, $this->getStartChar());

		// Checksum collector
		$checkSumCollector = $this->getKey($this->getStartChar());

		$this->img = @imagecreate($this->x, $this->y);
		
		if (!$this->img)
		{
			throw new \RuntimeException("Code128: Image failed to initialize");
		}
		
		$white = imagecolorallocate($this->img, 255, 255, 255);
		$black = imagecolorallocate($this->img, 0, 0, 0);

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
	}
}
?>