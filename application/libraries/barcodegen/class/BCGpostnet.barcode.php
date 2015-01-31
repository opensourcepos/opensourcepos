<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - PostNet
 *
 * A postnet is composed of either 5, 9 or 11 digits used by US postal service.
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode1D.php');

class BCGpostnet extends BCGBarcode1D {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $this->code = array(
            '11000',    /* 0 */
            '00011',    /* 1 */
            '00101',    /* 2 */
            '00110',    /* 3 */
            '01001',    /* 4 */
            '01010',    /* 5 */
            '01100',    /* 6 */
            '10001',    /* 7 */
            '10010',    /* 8 */
            '10100'     /* 9 */
        );

        $this->setThickness(9);
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        // Checksum
        $checksum = 0;
        $c = strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            $checksum += intval($this->text[$i]);
        }

        $checksum = 10 - ($checksum % 10);

        // Starting Code
        $this->drawChar($im, '1');

        // Code
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->findCode($this->text[$i]));
        }

        // Checksum
        $this->drawChar($im, $this->findCode($checksum));

        // Ending Code
        $this->drawChar($im, '1');
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
        $startlength = 3;
        $textlength = $c * 5 * 3;
        $checksumlength = 5 * 3;
        $endlength = 3;

        // We remove the white on the right
        $removelength = -1.56;

        $w += $startlength + $textlength + $checksumlength + $endlength + $removelength;
        $h += $this->thickness;
        return parent::getDimension($w, $h);
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        $c = strlen($this->text);
        if ($c === 0) {
            throw new BCGParseException('postnet', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('postnet', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        // Must contain 5, 9 or 11 chars
        if ($c !== 5 && $c !== 9 && $c !== 11) {
            throw new BCGParseException('postnet', 'Must contain 5, 9, or 11 characters.');
        }

        parent::validate();
    }

    /**
     * Overloaded method for drawing special barcode.
     *
     * @param resource $im
     * @param string $code
     * @param boolean $startBar
     */
    protected function drawChar($im, $code, $startBar = true) {
        $c = strlen($code);
        for ($i = 0; $i < $c; $i++) {
            if ($code[$i] === '0') {
                $posY = $this->thickness - ($this->thickness / 2.5);
            } else {
                $posY = 0;
            }

            $this->drawFilledRectangle($im, $this->positionX, $posY, $this->positionX + 0.44, $this->thickness - 1, BCGBarcode::COLOR_FG);
            $this->positionX += 3;
        }
    }
}
?>