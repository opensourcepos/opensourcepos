<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - UPC Supplemental Barcode 2 digits
 *
 * Working with UPC-A, UPC-E, EAN-13, EAN-8
 * This includes 2 digits (normaly for publications)
 * Must be placed next to UPC or EAN Code
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode1D.php');
include_once('BCGLabel.php');

class BCGupcext2 extends BCGBarcode1D {
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

        // Parity, 0=Odd, 1=Even. Depending on ?%4
        $this->codeParity = array(
            array(0, 0),    /* 0 */
            array(0, 1),    /* 1 */
            array(1, 0),    /* 2 */
            array(1, 1)     /* 3 */
        );
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        // Starting Code
        $this->drawChar($im, '001', true);

        // Code
        for ($i = 0; $i < 2; $i++) {
            $this->drawChar($im, self::inverse($this->findCode($this->text[$i]), $this->codeParity[intval($this->text) % 4][$i]), false);
            if ($i === 0) {
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
        $textlength = 2 * 7;
        $intercharlength = 2;

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
            throw new BCGParseException('upcext2', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('upcext2', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        // Must contain 2 digits
        if ($c !== 2) {
            throw new BCGParseException('upcext2', 'Must contain 2 digits.');
        }

        parent::validate();
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