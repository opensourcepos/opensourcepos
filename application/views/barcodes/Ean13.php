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
	 * Generate EAN13 code out of a provided number
	 * Code taken from http://stackoverflow.com/questions/19890144/generate-valid-ean13-in-php (unknown copyright / license claims)
	 * 
	 * @param number is the internal code you want to have EANed. The prefix, zero-padding and checksum are added by the function.
	 * @return string with complete EAN13 code
	 */
	private function generateEAN($number)
	{
		$code = '200' . str_pad($number, 9, '0');
		$weightflag = true;
		$sum = 0;
		
		// Weight for a digit in the checksum is 3, 1, 3.. starting from the last digit. 
		// loop backwards to make the loop length-agnostic. The same basic functionality 
		// will work for codes of different lengths.
		for ($i = strlen($code) - 1; $i >= 0; $i--)
		{
			$sum += (int)$code[$i] * ($weightflag?3:1);
			$weightflag = !$weightflag;
		}
		$code .= (10 - ($sum % 10)) % 10;
		
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
		generateEAN($this->data);
	
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


    /**
     * Draws a EAN 13 image barcode
     *
     * @return resource            The corresponding EAN13 image barcode
     *
     * @access public
     *
     * @author     Didier Fournout <didier.fournout@nyc.fr>
     */

     public function draw()

    {

        $text     = $this->getBarcode();

        $writer   = $this->getWriter();

        $fontsize = $this->getFontSize();

 

        // Calculate the barcode width

        $barcodewidth = (strlen($text)) * (7 * $this->getBarcodeWidth())

            + 3 * $this->getBarcodeWidth()  // left

            + 5 * $this->getBarcodeWidth()  // center

            + 3 * $this->getBarcodeWidth() // right

            + $writer->imagefontwidth($fontsize) + 1

            ;

 

        $barcodelongheight = (int)($writer->imagefontheight($fontsize) / 2) + $this->getBarcodeHeight();

 

        // Create the image

        $img = $writer->imagecreate(

            $barcodewidth,

            $barcodelongheight + $writer->imagefontheight($fontsize) + 1

        );

 

        // Alocate the black and white colors

        $black = $writer->imagecolorallocate($img, 0, 0, 0);

        $white = $writer->imagecolorallocate($img, 255, 255, 255);

 

        // Fill image with white color

        $writer->imagefill($img, 0, 0, $white);

 

        // get the first digit which is the key for creating the first 6 bars

        $key = substr($text, 0, 1);

 

        // Initiate x position

        $xpos = 0;

 

        // print first digit

        if ($this->showText) {

            $writer->imagestring(

                $img,

                $fontsize,

                $xpos,

                $this->getBarcodeHeight(),

                $key,

                $black

            );

 

            $xpos = $writer->imagefontwidth($fontsize) + 1;

        };

 

 

        // Draws the left guard pattern (bar-space-bar)

        // bar

        $writer->imagefilledrectangle(

            $img,

            $xpos,

            0,

            $xpos + $this->getBarcodeWidth() - 1,

            $barcodelongheight, 

            $black

        );

        $xpos += $this->getBarcodeWidth();

        // space

        $xpos += $this->getBarcodeWidth();

        // bar

        $writer->imagefilledrectangle(

            $img,

            $xpos,

            0,

            $xpos + $this->getBarcodeWidth() - 1,

            $barcodelongheight, 

            $black

        );

        $xpos += $this->getBarcodeWidth();

 

        // Draw left $text contents

        $set_array = $this->_codingmapleft[$key];

        for ($idx = 1; $idx < 7; $idx ++) {

            $value = substr($text, $idx, 1);

 

            if ($this->showText) {

                $writer->imagestring(

                    $img,

                    $fontsize,

                    $xpos + 1, 

                    $this->getBarcodeHeight(), 

                    $value, 

                    $black

                );

            }

 

            foreach ($this->_codingmap[$value][$set_array[$idx - 1]] as $bar) {

                if ($bar) {

                    $writer->imagefilledrectangle(

                        $img,

                        $xpos,

                        0,

                        $xpos + $this->getBarcodeWidth() - 1,

                        $this->getBarcodeHeight(),

                        $black

                    );

                }

                $xpos += $this->getBarcodeWidth();

            }

        }

 

        // Draws the center pattern (space-bar-space-bar-space)

        // space

        $xpos += $this->getBarcodeWidth();

        // bar

        $writer->imagefilledrectangle(

            $img,

            $xpos,

            0,

            $xpos + $this->getBarcodeWidth() - 1,

            $barcodelongheight,

            $black

        );

        $xpos += $this->getBarcodeWidth();

        // space

        $xpos += $this->getBarcodeWidth();

        // bar

        $writer->imagefilledrectangle(

            $img,

            $xpos,

            0,

            $xpos + $this->getBarcodeWidth() - 1,

            $barcodelongheight,

            $black

        );

        $xpos += $this->getBarcodeWidth();

        // space

        $xpos += $this->getBarcodeWidth();

 

 

        // Draw right $text contents

        for ($idx = 7; $idx < 13; $idx ++) {

            $value = substr($text, $idx, 1);

 

            if ($this->showText) {

                $writer->imagestring(

                    $img,

                    $fontsize,

                    $xpos + 1,

                    $this->getBarcodeHeight(),

                    $value,

                    $black

                );

            }

 

            foreach ($this->_codingmap[$value]['C'] as $bar) {

                if ($bar) {

                    $writer->imagefilledrectangle(

                        $img,

                        $xpos,

                        0,

                        $xpos + $this->getBarcodeWidth() - 1,

                        $this->getBarcodeHeight(),

                        $black

                    );

                }

                $xpos += $this->getBarcodeWidth();

            }

        }

 

        // Draws the right guard pattern (bar-space-bar)

        // bar

        $writer->imagefilledrectangle(

            $img,

            $xpos,

            0,

            $xpos + $this->getBarcodeWidth() - 1,

            $barcodelongheight, 

            $black

        );

        $xpos += $this->getBarcodeWidth();

        // space

        $xpos += $this->getBarcodeWidth();

        // bar

        $writer->imagefilledrectangle(

            $img,

            $xpos,

            0,

            $xpos + $this->getBarcodeWidth() - 1,

            $barcodelongheight, 

            $black

        );

 

        return $img;

    } // function create