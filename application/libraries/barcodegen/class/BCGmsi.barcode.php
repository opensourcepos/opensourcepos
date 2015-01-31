<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - MSI Plessey
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGArgumentException.php');
include_once('BCGBarcode1D.php');

class BCGmsi extends BCGBarcode1D {
    private $checksum;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $this->code = array(
            '01010101',     /* 0 */
            '01010110',     /* 1 */
            '01011001',     /* 2 */
            '01011010',     /* 3 */
            '01100101',     /* 4 */
            '01100110',     /* 5 */
            '01101001',     /* 6 */
            '01101010',     /* 7 */
            '10010101',     /* 8 */
            '10010110'      /* 9 */
        );

        $this->setChecksum(0);
    }

    /**
     * Sets how many checksums we display. 0 to 2.
     *
     * @param int $checksum
     */
    public function setChecksum($checksum) {
        $checksum = intval($checksum);
        if ($checksum < 0 && $checksum > 2) {
            throw new BCGArgumentException('The checksum must be between 0 and 2 included.', 'checksum');
        }

        $this->checksum = $checksum;
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        // Checksum
        $this->calculateChecksum();

        // Starting Code
        $this->drawChar($im, '10', true);

        // Chars
        $c = strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->findCode($this->text[$i]), true);
        }

        $c = count($this->checksumValue);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->findCode($this->checksumValue[$i]), true);
        }

        // Ending Code
        $this->drawChar($im, '010', true);
        $this->drawText($im, 0, 0, $this->positionX, $this->thickness);
    }

    /**
     * Returns the maximal size of a barcode.
     *
     * @param int $w
     * @param int $h
     * @return int[]
     */
    public function getDimension($w, $h) {
        $textlength = 12 * strlen($this->text);
        $startlength = 3;
        $checksumlength = $this->checksum * 12;
        $endlength = 4;

        $w += $startlength + $textlength + $checksumlength + $endlength;
        $h += $this->thickness;
        return parent::getDimension($w, $h);
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        $c = strlen($this->text);
        if ($c === 0) {
            throw new BCGParseException('msi', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('msi', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }
    }

    /**
     * Overloaded method to calculate checksum.
     */
    protected function calculateChecksum() {
        // Forming a new number
        // If the original number is even, we take all even position
        // If the original number is odd, we take all odd position
        // 123456 = 246
        // 12345 = 135
        // Multiply by 2
        // Add up all the digit in the result (270 : 2+7+0)
        // Add up other digit not used.
        // 10 - (? Modulo 10). If result = 10, change to 0
        $last_text = $this->text;
        $this->checksumValue = array();
        for ($i = 0; $i < $this->checksum; $i++) {
            $new_text = '';
            $new_number = 0;
            $c = strlen($last_text);
            if ($c % 2 === 0) { // Even
                $starting = 1;
            } else {
                $starting = 0;
            }

            for ($j = $starting; $j < $c; $j += 2) {
                $new_text .= $last_text[$j];
            }

            $new_text = strval(intval($new_text) * 2);
            $c2 = strlen($new_text);
            for ($j = 0; $j < $c2; $j++) {
                $new_number += intval($new_text[$j]);
            }

            for ($j = ($starting === 0) ? 1 : 0; $j < $c; $j += 2) {
                $new_number += intval($last_text[$j]);
            }

            $new_number = (10 - $new_number % 10) % 10;
            $this->checksumValue[] = $new_number;
            $last_text .= $new_number;
        }
    }

    /**
     * Overloaded method to display the checksum.
     */
    protected function processChecksum() {
        if ($this->checksumValue === false) { // Calculate the checksum only once
            $this->calculateChecksum();
        }

        if ($this->checksumValue !== false) {
            $ret = '';
            $c = count($this->checksumValue);
            for ($i = 0; $i < $c; $i++) {
                $ret .= $this->keys[$this->checksumValue[$i]];
            }

            return $ret;
        }

        return false;
    }
}
?>