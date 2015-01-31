<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - UPC Supplemental Barcode 2 digits
 *
 * Working with UPC-A, UPC-E, EAN-13, EAN-8
 * This includes 5 digits (normaly for suggested retail price)
 * Must be placed next to UPC or EAN Code
 * If 90000 -> No suggested Retail Price
 * If 99991 -> Book Complimentary (normally free)
 * If 90001 to 98999 -> Internal Purpose of Publisher
 * If 99990 -> Used by the National Association of College Stores to mark used books
 * If 0xxxx -> Price Expressed in British Pounds (xx.xx)
 * If 5xxxx -> Price Expressed in U.S. dollars (US$xx.xx)
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode1D.php');
include_once('BCGLabel.php');

class BCGupcext5 extends BCGBarcode1D {
    protected $codeParity = array();

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $this->code = array(
            '2100',     /* 0 */
            '1110',     /* 1 */
            '1011',     /* 2 */
            '0300',     /* 3 */
            '0021',     /* 4 */
            '0120',     /* 5 */
            '0003',     /* 6 */
            '0201',     /* 7 */
            '0102',     /* 8 */
            '2001'      /* 9 */
        );

        // Parity, 0=Odd, 1=Even. Depending Checksum
        $this->codeParity = array(
            array(1, 1, 0, 0, 0),   /* 0 */
            array(1, 0, 1, 0, 0),   /* 1 */
            array(1, 0, 0, 1, 0),   /* 2 */
            array(1, 0, 0, 0, 1),   /* 3 */
            array(0, 1, 1, 0, 0),   /* 4 */
            array(0, 0, 1, 1, 0),   /* 5 */
            array(0, 0, 0, 1, 1),   /* 6 */
            array(0, 1, 0, 1, 0),   /* 7 */
            array(0, 1, 0, 0, 1),   /* 8 */
            array(0, 0, 1, 0, 1)    /* 9 */
        );
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
        $this->drawChar($im, '001', true);

        // Code
        for ($i = 0; $i < 5; $i++) {
            $this->drawChar($im, self::inverse($this->findCode($this->text[$i]), $this->codeParity[$this->checksumValue][$i]), false);
            if ($i < 4) {
                $this->drawChar($im, '00', false);    // Inter-char
            }
        }

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
        $startlength = 4;
        $textlength = 5 * 7;
        $intercharlength = 2 * 4;

        $w += $startlength + $textlength + $intercharlength;
        $h += $this->thickness;
        return parent::getDimension($w, $h);
    }

    /**
     * Adds the default label.
     */
    protected function addDefaultLabel() {
        parent::addDefaultLabel();

        if ($this->defaultLabel !== null) {
            $this->defaultLabel->setPosition(BCGLabel::POSITION_TOP);
        }
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        $c = strlen($this->text);
        if ($c === 0) {
            throw new BCGParseException('upcext5', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('upcext5', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        // Must contain 5 digits
        if ($c !== 5) {
            throw new BCGParseException('upcext5', 'Must contain 5 digits.');
        }

        parent::validate();
    }

    /**
     * Overloaded method to calculate checksum.
     */
    protected function calculateChecksum() {
        // Calculating Checksum
        // Consider the right-most digit of the message to be in an "odd" position,
        // and assign odd/even to each character moving from right to left
        // Odd Position = 3, Even Position = 9
        // Multiply it by the number
        // Add all of that and do ?mod10
        $odd = true;
        $this->checksumValue = 0;
        $c = strlen($this->text);
        for ($i = $c; $i > 0; $i--) {
            if ($odd === true) {
                $multiplier = 3;
                $odd = false;
            } else {
                $multiplier = 9;
                $odd = true;
            }

            if (!isset($this->keys[$this->text[$i - 1]])) {
                return;
            }

            $this->checksumValue += $this->keys[$this->text[$i - 1]] * $multiplier;
        }

        $this->checksumValue = $this->checksumValue % 10;
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

    /**
     * Inverses the string when the $inverse parameter is equal to 1.
     *
     * @param string $text
     * @param int $inverse
     * @return string
     */
    private static function inverse($text, $inverse = 1) {
        if ($inverse === 1) {
            $text = strrev($text);
        }

        return $text;
    }
}
?>