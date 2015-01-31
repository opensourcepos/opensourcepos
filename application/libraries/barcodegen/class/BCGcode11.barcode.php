<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - Code 11
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode1D.php');

class BCGcode11 extends BCGBarcode1D {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-');
        $this->code = array(    // 0 added to add an extra space
            '000010',   /* 0 */
            '100010',   /* 1 */
            '010010',   /* 2 */
            '110000',   /* 3 */
            '001010',   /* 4 */
            '101000',   /* 5 */
            '011000',   /* 6 */
            '000110',   /* 7 */
            '100100',   /* 8 */
            '100000',   /* 9 */
            '001000'    /* - */
        );
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        // Starting Code
        $this->drawChar($im, '001100', true);

        // Chars
        $c = strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->findCode($this->text[$i]), true);
        }

        // Checksum
        $this->calculateChecksum();
        $c = count($this->checksumValue);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->code[$this->checksumValue[$i]], true);
        }

        // Ending Code
        $this->drawChar($im, '00110', true);
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
        $startlength = 8;

        $textlength = 0;
        $c = strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            $textlength += $this->getIndexLength($this->findIndex($this->text[$i]));
        }

        $checksumlength = 0;
        $this->calculateChecksum();
        $c = count($this->checksumValue);
        for ($i = 0; $i < $c; $i++) {
            $checksumlength += $this->getIndexLength($this->checksumValue[$i]);
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
            throw new BCGParseException('code11', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('code11', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        parent::validate();
    }

    /**
     * Overloaded method to calculate checksum.
     */
    protected function calculateChecksum() {
        // Checksum
        // First CheckSUM "C"
        // The "C" checksum character is the modulo 11 remainder of the sum of the weighted
        // value of the data characters. The weighting value starts at "1" for the right-most
        // data character, 2 for the second to last, 3 for the third-to-last, and so on up to 20.
        // After 10, the sequence wraps around back to 1.

        // Second CheckSUM "K"
        // Same as CheckSUM "C" but we count the CheckSum "C" at the end
        // After 9, the sequence wraps around back to 1.
        $sequence_multiplier = array(10, 9);
        $temp_text = $this->text;
        $this->checksumValue = array();
        for ($z = 0; $z < 2; $z++) {
            $c = strlen($temp_text);

            // We don't display the K CheckSum if the original text had a length less than 10
            if ($c <= 10 && $z === 1) {
                break;
            }

            $checksum = 0;
            for ($i = $c, $j = 0; $i > 0; $i--, $j++) {
                $multiplier = $i % $sequence_multiplier[$z];
                if ($multiplier === 0) {
                    $multiplier = $sequence_multiplier[$z];
                }

                $checksum += $this->findIndex($temp_text[$j]) * $multiplier;
            }

            $this->checksumValue[$z] = $checksum % 11;
            $temp_text .= $this->keys[$this->checksumValue[$z]];
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

    private function getIndexLength($index) {
        $length = 0;
        if ($index !== false) {
            $length += 6;
            $length += substr_count($this->code[$index], '1');
        }

        return $length;
    }
}
?>