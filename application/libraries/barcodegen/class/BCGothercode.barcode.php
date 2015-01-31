<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - othercode
 *
 * Other Codes
 * Starting with a bar and altern to space, bar, ...
 * 0 is the smallest
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode1D.php');

class BCGothercode extends BCGBarcode1D {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        $this->drawChar($im, $this->text, true);
        $this->drawText($im, 0, 0, $this->positionX, $this->thickness);
    }

    /**
     * Gets the label.
     * If the label was set to BCGBarcode1D::AUTO_LABEL, the label will display the value from the text parsed.
     *
     * @return string
     */
    public function getLabel() {
        $label = $this->label;
        if ($this->label === BCGBarcode1D::AUTO_LABEL) {
            $label = '';
        }

        return $label;
    }

    /**
     * Returns the maximal size of a barcode.
     *
     * @param int $w
     * @param int $h
     * @return int[]
     */
    public function getDimension($w, $h) {
        $array = str_split($this->text, 1);
        $textlength = array_sum($array) + count($array);

        $w += $textlength;
        $h += $this->thickness;
        return parent::getDimension($w, $h);
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        $c = strlen($this->text);
        if ($c === 0) {
            throw new BCGParseException('othercode', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('othercode', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        parent::validate();
    }
}
?>