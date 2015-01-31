<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - Codabar
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode1D.php');

class BCGcodabar extends BCGBarcode1D {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-', '$', ':', '/', '.', '+', 'A', 'B', 'C', 'D');
        $this->code = array(    // 0 added to add an extra space
            '00000110',     /* 0 */
            '00001100',     /* 1 */
            '00010010',     /* 2 */
            '11000000',     /* 3 */
            '00100100',     /* 4 */
            '10000100',     /* 5 */
            '01000010',     /* 6 */
            '01001000',     /* 7 */
            '01100000',     /* 8 */
            '10010000',     /* 9 */
            '00011000',     /* - */
            '00110000',     /* $ */
            '10001010',     /* : */
            '10100010',     /* / */
            '10101000',     /* . */
            '00111110',     /* + */
            '00110100',     /* A */
            '01010010',     /* B */
            '00010110',     /* C */
            '00011100'      /* D */
        );
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
        $c = strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->findCode($this->text[$i]), true);
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
        $textLength = 0;
        $c = strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            $index = $this->findIndex($this->text[$i]);
            if ($index !== false) {
                $textLength += 8;
                $textLength += substr_count($this->code[$index], '1');
            }
        }

        $w += $textLength;
        $h += $this->thickness;
        return parent::getDimension($w, $h);
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        $c = strlen($this->text);
        if ($c === 0) {
            throw new BCGParseException('codabar', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('codabar', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        // Must start by A, B, C or D
        if ($c == 0 || ($this->text[0] !== 'A' && $this->text[0] !== 'B' && $this->text[0] !== 'C' && $this->text[0] !== 'D')) {
            throw new BCGParseException('codabar', 'The text must start by the character A, B, C, or D.');
        }

        // Must end by A, B, C or D
        $c2 = $c - 1;
        if ($c2 === 0 || ($this->text[$c2] !== 'A' && $this->text[$c2] !== 'B' && $this->text[$c2] !== 'C' && $this->text[$c2] !== 'D')) {
            throw new BCGParseException('codabar', 'The text must end by the character A, B, C, or D.');
        }

        parent::validate();
    }
}
?>