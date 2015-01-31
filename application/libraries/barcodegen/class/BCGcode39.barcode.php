<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - Code 39
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode1D.php');

class BCGcode39 extends BCGBarcode1D {
    protected $starting, $ending;
    protected $checksum;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->starting = $this->ending = 43;
        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%', '*');
        $this->code = array(    // 0 added to add an extra space
            '0001101000',   /* 0 */
            '1001000010',   /* 1 */
            '0011000010',   /* 2 */
            '1011000000',   /* 3 */
            '0001100010',   /* 4 */
            '1001100000',   /* 5 */
            '0011100000',   /* 6 */
            '0001001010',   /* 7 */
            '1001001000',   /* 8 */
            '0011001000',   /* 9 */
            '1000010010',   /* A */
            '0010010010',   /* B */
            '1010010000',   /* C */
            '0000110010',   /* D */
            '1000110000',   /* E */
            '0010110000',   /* F */
            '0000011010',   /* G */
            '1000011000',   /* H */
            '0010011000',   /* I */
            '0000111000',   /* J */
            '1000000110',   /* K */
            '0010000110',   /* L */
            '1010000100',   /* M */
            '0000100110',   /* N */
            '1000100100',   /* O */
            '0010100100',   /* P */
            '0000001110',   /* Q */
            '1000001100',   /* R */
            '0010001100',   /* S */
            '0000101100',   /* T */
            '1100000010',   /* U */
            '0110000010',   /* V */
            '1110000000',   /* W */
            '0100100010',   /* X */
            '1100100000',   /* Y */
            '0110100000',   /* Z */
            '0100001010',   /* - */
            '1100001000',   /* . */
            '0110001000',   /*   */
            '0101010000',   /* $ */
            '0101000100',   /* / */
            '0100010100',   /* + */
            '0001010100',   /* % */
            '0100101000'    /* * */
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
     * Parses the text before displaying it.
     *
     * @param mixed $text
     */
    public function parse($text) {
        parent::parse(strtoupper($text));    // Only Capital Letters are Allowed
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        // Starting *
        $this->drawChar($im, $this->code[$this->starting], true);

        // Chars
        $c =  strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->findCode($this->text[$i]), true);
        }

        // Checksum (rarely used)
        if ($this->checksum === true) {
            $this->calculateChecksum();
            $this->drawChar($im, $this->code[$this->checksumValue % 43], true);
        }

        // Ending *
        $this->drawChar($im, $this->code[$this->ending], true);
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
        $textlength = 13 * strlen($this->text);
        $startlength = 13;
        $checksumlength = 0;
        if ($this->checksum === true) {
            $checksumlength = 13;
        }

        $endlength = 13;

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
            throw new BCGParseException('code39', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('code39', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        if (strpos($this->text, '*') !== false) {
            throw new BCGParseException('code39', 'The character \'*\' is not allowed.');
        }

        parent::validate();
    }

    /**
     * Overloaded method to calculate checksum.
     */
    protected function calculateChecksum() {
        $this->checksumValue = 0;
        $c = strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            $this->checksumValue += $this->findIndex($this->text[$i]);
        }

        $this->checksumValue = $this->checksumValue % 43;
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