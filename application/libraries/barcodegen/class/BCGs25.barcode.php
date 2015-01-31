<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - Standard 2 of 5
 *
 * TODO I25 and S25 -> 1/3 or 1/2 for the big bar
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode1D.php');

class BCGs25 extends BCGBarcode1D {
    private $checksum;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $this->code = array(
            '0000202000',   /* 0 */
            '2000000020',   /* 1 */
            '0020000020',   /* 2 */
            '2020000000',   /* 3 */
            '0000200020',   /* 4 */
            '2000200000',   /* 5 */
            '0020200000',   /* 6 */
            '0000002020',   /* 7 */
            '2000002000',   /* 8 */
            '0020002000'    /* 9 */
        );

        $this->setChecksum(false);
    }

    /**
     * Sets if we display the checksum.
     *
     * @param bool $checksum
     */
    public function setChecksum($checksum) {
        $this->checksum = (bool)$checksum;
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        $temp_text = $this->text;

        // Checksum
        if ($this->checksum === true) {
            $this->calculateChecksum();
            $temp_text .= $this->keys[$this->checksumValue];
        }

        // Starting Code
        $this->drawChar($im, '101000', true);

        // Chars
        $c = strlen($temp_text);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->findCode($temp_text[$i]), true);
        }

        // Ending Code
        $this->drawChar($im, '10001', true);
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
        $c = strlen($this->text);
        $startlength = 8;
        $textlength = $c * 14;
        $checksumlength = 0;
        if ($c % 2 !== 0) {
            $checksumlength = 14;
        }

        $endlength = 7;

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
            throw new BCGParseException('s25', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('s25', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        // Must be even
        if ($c % 2 !== 0 && $this->checksum === false) {
            throw new BCGParseException('s25', 's25 must contain an even amount of digits if checksum is false.');
        } elseif ($c % 2 === 0 && $this->checksum === true) {
            throw new BCGParseException('s25', 's25 must contain an odd amount of digits if checksum is true.');
        }

        parent::validate();
    }

    /**
     * Overloaded method to calculate checksum.
     */
    protected function calculateChecksum() {
        // Calculating Checksum
        // Consider the right-most digit of the message to be in an "even" position,
        // and assign odd/even to each character moving from right to left
        // Even Position = 3, Odd Position = 1
        // Multiply it by the number
        // Add all of that and do 10-(?mod10)
        $even = true;
        $this->checksumValue = 0;
        $c = strlen($this->text);
        for ($i = $c; $i > 0; $i--) {
            if ($even === true) {
                $multiplier = 3;
                $even = false;
            } else {
                $multiplier = 1;
                $even = true;
            }

            $this->checksumValue += $this->keys[$this->text[$i - 1]] * $multiplier;
        }
        $this->checksumValue = (10 - $this->checksumValue % 10) % 10;
    }

    /**
     * Overloaded method to display the checksum.
     */
    protected function processChecksum() {
        if ($this->checksumValue === false) { // Calculate the checksum only once
            $this->calculateChecksum();
        }

        if ($this->checksumValue !== false) {
            return $this->keys[$this->checksumValue];
        }

        return false;
    }
}
?>