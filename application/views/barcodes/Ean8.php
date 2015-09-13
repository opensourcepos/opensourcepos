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
 
/**
 * Image_Barcode2_Driver_Ean8 class
 *
 * Renders EAN 8 barcodes
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
 * emberlabs Barcode Creator - Ean8
 * 	     Generate Ean8 Barcodes
 *
 *
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/samt/barcode
 */
class Ean8 extends BarcodeBase
{
	/*
	 * @var data - to be set
	 */
	private $data = '';
	
    /*
     * Coding map
     * @var array 
     */
    private $_codingmap = array(
        '0' => array(
            'A' => array(0,0,0,1,1,0,1),
            'C' => array(1,1,1,0,0,1,0)
        ),
        '1' => array(
            'A' => array(0,0,1,1,0,0,1),
            'C' => array(1,1,0,0,1,1,0)
        ),
        '2' => array(
            'A' => array(0,0,1,0,0,1,1),
            'C' => array(1,1,0,1,1,0,0)
        ),
        '3' => array(
            'A' => array(0,1,1,1,1,0,1),
            'C' => array(1,0,0,0,0,1,0)
        ),
        '4' => array(
            'A' => array(0,1,0,0,0,1,1),
            'C' => array(1,0,1,1,1,0,0)
        ),
        '5' => array(
            'A' => array(0,1,1,0,0,0,1),
            'C' => array(1,0,0,1,1,1,0)
        ),
        '6' => array(
            'A' => array(0,1,0,1,1,1,1),
            'C' => array(1,0,1,0,0,0,0)
        ),
        '7' => array(
            'A' => array(0,1,1,1,0,1,1),
            'C' => array(1,0,0,0,1,0,0)
        ),
        '8' => array(
            'A' => array(0,1,1,0,1,1,1),
            'C' => array(1,0,0,1,0,0,0)
        ),
        '9' => array(
            'A' => array(0,0,0,1,0,1,1),
            'C' => array(1,1,1,0,1,0,0)
        )
    );

	/*
	 * Calculate EAN8 or EAN13 automatically
	 * set $len = 8 for EAN8, $len = 13 for EAN13
	 * 
	 * @param number is the internal code you want to have EANed. The prefix, zero-padding and checksum are added by the function.
	 * @return string with complete EAN13 code
	 */
	private function generateEAN($number, $len = 8)
	{
		$code = null;
	
		if($number > -1)
		{
			$data_len = $len - 1;
			$code = $number;
			
			//Padding
			$code = str_pad($code, $data_len, '0', STR_PAD_LEFT);
			$code_len = strlen($code);
			
			// calculate check digit
			$sum_a = 0;
			for ($i = 1; $i < $data_len; $i += 2)
			{
			    $sum_a += $code{$i};
			}
			
			if ($len > 12)
			{
			    $sum_a *= 3;
			}
			
			$sum_b = 0;
			for ($i = 0; $i < $data_len; $i += 2)
			{
			    $sum_b += ($code{$i});
			}
			
			if ($len < 13)
			{
			    $sum_b *= 3;
			}
			
			$r = ($sum_a + $sum_b) % 10;
			
			if($r > 0)
			{
			    $r = (10 - $r);
			}
			
			if ($code_len == $data_len)
			{
			    // add check digit
			    $code .= $r;
			}
			elseif ($r !== intval($code{$data_len}))
			{
			    // wrong checkdigit
			    $code = null;
			}
		}

		return $code;
	}

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
		$code = $this->generateEAN($this->data);

		// Bars is in reference to a single, 1-level bar
		$pxPerBar = 2.5;
		
        // Calculate the barcode width
        $barcodewidth = (strlen($code)) * (7 * $pxPerBar)
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
        $key = substr($code, 0, 1);

        // Initiate x position
        $xpos = 0;
 
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

        for ($idx = 0; $idx < 4; $idx ++)
		{
            $value = substr($code, $idx, 1);

            foreach ($this->_codingmap[$value]['A'] as $bar)
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

        // Draw right $code contents
        for ($idx = 4; $idx < 8; $idx ++)
		{
            $value = substr($code, $idx, 1);

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