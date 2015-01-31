<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - UPC-E
 *
 * You can provide a UPC-A code (without dash), the code will transform
 * it into a UPC-E format if it's possible.
 * UPC-E contains
 *    - 1 system digits (not displayed but coded with parity)
 *    - 6 digits
 *    - 1 checksum digit (not displayed but coded with parity)
 *
 * The text returned is the UPC-E without the checksum.
 * The checksum is always displayed.
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode.php');
include_once('BCGBarcode1D.php');
include_once('BCGLabel.php');

class BCGupce extends BCGBarcode1D {
    protected $codeParity = array();
    protected $upce;
    protected $labelLeft = null;
    protected $labelCenter = null;
    protected $labelRight = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        // Odd Parity starting with a space
        // Even Parity is the inverse (0=0012) starting with a space
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

        // Parity, 0=Odd, 1=Even for manufacturer code. Depending on 1st System Digit and Checksum
        $this->codeParity = array(
            array(
                array(1, 1, 1, 0, 0, 0),    /* 0,0 */
                array(1, 1, 0, 1, 0, 0),    /* 0,1 */
                array(1, 1, 0, 0, 1, 0),    /* 0,2 */
                array(1, 1, 0, 0, 0, 1),    /* 0,3 */
                array(1, 0, 1, 1, 0, 0),    /* 0,4 */
                array(1, 0, 0, 1, 1, 0),    /* 0,5 */
                array(1, 0, 0, 0, 1, 1),    /* 0,6 */
                array(1, 0, 1, 0, 1, 0),    /* 0,7 */
                array(1, 0, 1, 0, 0, 1),    /* 0,8 */
                array(1, 0, 0, 1, 0, 1)     /* 0,9 */
            ),
            array(
                array(0, 0, 0, 1, 1, 1),    /* 0,0 */
                array(0, 0, 1, 0, 1, 1),    /* 0,1 */
                array(0, 0, 1, 1, 0, 1),    /* 0,2 */
                array(0, 0, 1, 1, 1, 0),    /* 0,3 */
                array(0, 1, 0, 0, 1, 1),    /* 0,4 */
                array(0, 1, 1, 0, 0, 1),    /* 0,5 */
                array(0, 1, 1, 1, 0, 0),    /* 0,6 */
                array(0, 1, 0, 1, 0, 1),    /* 0,7 */
                array(0, 1, 0, 1, 1, 0),    /* 0,8 */
                array(0, 1, 1, 0, 1, 0)     /* 0,9 */
            )
        );
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        $this->calculateChecksum();

        // Starting Code
        $this->drawChar($im, '000', true);
        $c = strlen($this->upce);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, self::inverse($this->findCode($this->upce[$i]), $this->codeParity[intval($this->text[0])][$this->checksumValue][$i]), false);
        }

        // Draw Center Guard Bar
        $this->drawChar($im, '00000', false);

        // Draw Right Bar
        $this->drawChar($im, '0', true);
        $this->text = $this->text[0] . $this->upce;
        $this->drawText($im, 0, 0, $this->positionX, $this->thickness);

        if ($this->isDefaultEanLabelEnabled()) {
            $dimension = $this->labelCenter->getDimension();
            $this->drawExtendedBars($im, $dimension[1] - 2);
        }
    }

    /**
     * Returns the maximal size of a barcode.
     *
     * @param int $w
     * @param int $h
     * @return int[]
     */
    public function getDimension($w, $h) {
        $startlength = 3;
        $centerlength = 5;
        $textlength = 6 * 7;
        $endlength = 1;

        $w += $startlength + $centerlength + $textlength + $endlength;
        $h += $this->thickness;
        return parent::getDimension($w, $h);
    }

    /**
     * Adds the default label.
     */
    protected function addDefaultLabel() {
        if ($this->isDefaultEanLabelEnabled()) {
            $this->processChecksum();
            $font = $this->font;

            $this->labelLeft = new BCGLabel(substr($this->text, 0, 1), $font, BCGLabel::POSITION_LEFT, BCGLabel::ALIGN_BOTTOM);
            $labelLeftDimension = $this->labelLeft->getDimension();
            $this->labelLeft->setSpacing(8);
            $this->labelLeft->setOffset($labelLeftDimension[1] / 2);

            $this->labelCenter = new BCGLabel($this->upce, $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
            $labelCenterDimension = $this->labelCenter->getDimension();
            $this->labelCenter->setOffset(($this->scale * 46 - $labelCenterDimension[0]) / 2 + $this->scale * 2);

            $this->labelRight = new BCGLabel($this->keys[$this->checksumValue], $font, BCGLabel::POSITION_RIGHT, BCGLabel::ALIGN_BOTTOM);
            $labelRightDimension = $this->labelRight->getDimension();
            $this->labelRight->setSpacing(8);
            $this->labelRight->setOffset($labelRightDimension[1] / 2);

            $this->addLabel($this->labelLeft);
            $this->addLabel($this->labelCenter);
            $this->addLabel($this->labelRight);
        }
    }

    /**
     * Checks if the default ean label is enabled.
     *
     * @return bool
     */
    protected function isDefaultEanLabelEnabled() {
        $label = $this->getLabel();
        $font = $this->font;
        return $label !== null && $label !== '' && $font !== null && $this->defaultLabel !== null;
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        $c = strlen($this->text);
        if ($c === 0) {
            throw new BCGParseException('upce', 'No data has been entered.');
        }

        // Checking if all chars are allowed
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('upce', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }

        // Must contain 11 chars
        // Must contain 6 chars (if starting with upce directly)
        // First Chars must be 0 or 1
        if ($c !== 11 && $c !== 6) {
            throw new BCGParseException('upce', 'You must provide a UPC-A (11 characters) or a UPC-E (6 characters).');
        } elseif ($this->text[0] !== '0' && $this->text[0] !== '1' && $c !== 6) {
            throw new BCGParseException('upce', 'UPC-A must start with 0 or 1 to be converted to UPC-E.');
        }

        // Convert part
        $this->upce = '';
        if ($c !== 6) {
            // Checking if UPC-A is convertible
            $temp1 = substr($this->text, 3, 3);
            if ($temp1 === '000' || $temp1 === '100' || $temp1 === '200') { // manufacturer code ends with 100, 200 or 300
                if (substr($this->text, 6, 2) === '00') { // Product must start with 00
                    $this->upce = substr($this->text, 1, 2) . substr($this->text, 8, 3) . substr($this->text, 3, 1);
                }
            } elseif (substr($this->text, 4, 2) === '00') { // manufacturer code ends with 00
                if (substr($this->text, 6, 3) === '000') { // Product must start with 000
                    $this->upce = substr($this->text, 1, 3) . substr($this->text, 9, 2) . '3';
                }
            } elseif (substr($this->text, 5, 1) === '0') { // manufacturer code ends with 0
                if (substr($this->text, 6, 4) === '0000') { // Product must start with 0000
                    $this->upce = substr($this->text, 1, 4) . substr($this->text, 10, 1) . '4';
                }
            } else { // No zero leading at manufacturer code
                $temp2 = intval(substr($this->text, 10, 1));
                if (substr($this->text, 6, 4) === '0000' && $temp2 >= 5 && $temp2 <= 9) { // Product must start with 0000 and must end by 5, 6, 7, 8 or 9
                    $this->upce = substr($this->text, 1, 5) . substr($this->text, 10, 1);
                }
            }
        } else {
            $this->upce = $this->text;
        }

        if ($this->upce === '') {
            throw new BCGParseException('upce', 'Your UPC-A can\'t be converted to UPC-E.');
        }

        if ($c === 6) {
            $upca = '';

            // We convert UPC-E to UPC-A to find the checksum
            if ($this->text[5] === '0' || $this->text[5] === '1' || $this->text[5] === '2') {
                $upca = substr($this->text, 0, 2) . $this->text[5] . '0000' . substr($this->text, 2, 3);
            } elseif ($this->text[5] === '3') {
                $upca = substr($this->text, 0, 3) . '00000' . substr($this->text, 3, 2);
            } elseif ($this->text[5] === '4') {
                $upca = substr($this->text, 0, 4) . '00000' . $this->text[4];
            } else {
                $upca = substr($this->text, 0, 5) . '0000' . $this->text[5];
            }

            $this->text = '0' . $upca;
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
        // Odd Position = 3, Even Position = 1
        // Multiply it by the number
        // Add all of that and do 10-(?mod10)
        $odd = true;
        $this->checksumValue = 0;
        $c = strlen($this->text);
        for ($i = $c; $i > 0; $i--) {
            if ($odd === true) {
                $multiplier = 3;
                $odd = false;
            } else {
                $multiplier = 1;
                $odd = true;
            }

            if (!isset($this->keys[$this->text[$i - 1]])) {
                return;
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

    /**
     * Draws the extended bars on the image.
     *
     * @param resource $im
     * @param int $plus
     */
    protected function drawExtendedBars($im, $plus) {
        $rememberX = $this->positionX;
        $rememberH = $this->thickness;

        // We increase the bars
        $this->thickness = $this->thickness + intval($plus / $this->scale);
        $this->positionX = 0;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);
        $this->positionX += 2;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);

        // Last Bars
        $this->positionX += 46;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);
        $this->positionX += 2;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);

        $this->positionX = $rememberX;
        $this->thickness = $rememberH;
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