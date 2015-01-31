<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - EAN-13
 *
 * EAN-13 contains
 *    - 2 system digits (1 not displayed but coded with parity)
 *    - 5 manufacturer code digits
 *    - 5 product digits
 *    - 1 checksum digit
 *
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

class BCGean13 extends BCGBarcode1D {
    protected $codeParity = array();
    protected $labelLeft = null;
    protected $labelCenter1 = null;
    protected $labelCenter2 = null;
    protected $alignLabel;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        // Left-Hand Odd Parity starting with a space
        // Left-Hand Even Parity is the inverse (0=0012) starting with a space
        // Right-Hand is the same of Left-Hand starting with a bar
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

        // Parity, 0=Odd, 1=Even for manufacturer code. Depending on 1st System Digit
        $this->codeParity = array(
            array(0, 0, 0, 0, 0),   /* 0 */
            array(0, 1, 0, 1, 1),   /* 1 */
            array(0, 1, 1, 0, 1),   /* 2 */
            array(0, 1, 1, 1, 0),   /* 3 */
            array(1, 0, 0, 1, 1),   /* 4 */
            array(1, 1, 0, 0, 1),   /* 5 */
            array(1, 1, 1, 0, 0),   /* 6 */
            array(1, 0, 1, 0, 1),   /* 7 */
            array(1, 0, 1, 1, 0),   /* 8 */
            array(1, 1, 0, 1, 0)    /* 9 */
        );

        $this->alignDefaultLabel(true);
    }

    public function alignDefaultLabel($align) {
        $this->alignLabel = (bool)$align;
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        $this->drawBars($im);
        $this->drawText($im, 0, 0, $this->positionX, $this->thickness);

        if ($this->isDefaultEanLabelEnabled()) {
            $dimension = $this->labelCenter1->getDimension();
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
        $textlength = 12 * 7;
        $endlength = 3;

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
            $label = $this->getLabel();
            $font = $this->font;

            $this->labelLeft = new BCGLabel(substr($label, 0, 1), $font, BCGLabel::POSITION_LEFT, BCGLabel::ALIGN_BOTTOM);
            $this->labelLeft->setSpacing(4 * $this->scale);

            $this->labelCenter1 = new BCGLabel(substr($label, 1, 6), $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
            $labelCenter1Dimension = $this->labelCenter1->getDimension();
            $this->labelCenter1->setOffset(($this->scale * 44 - $labelCenter1Dimension[0]) / 2 + $this->scale * 2);

            $this->labelCenter2 = new BCGLabel(substr($label, 7, 5) . $this->keys[$this->checksumValue], $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
            $this->labelCenter2->setOffset(($this->scale * 44 - $labelCenter1Dimension[0]) / 2 + $this->scale * 48);

            if ($this->alignLabel) {
                $labelDimension = $this->labelCenter1->getDimension();
                $this->labelLeft->setOffset($labelDimension[1]);
            } else {
                $labelDimension = $this->labelLeft->getDimension();
                $this->labelLeft->setOffset($labelDimension[1] / 2);
            }

            $this->addLabel($this->labelLeft);
            $this->addLabel($this->labelCenter1);
            $this->addLabel($this->labelCenter2);
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
            throw new BCGParseException('ean13', 'No data has been entered.');
        }

        $this->checkCharsAllowed();
        $this->checkCorrectLength();

        parent::validate();
    }

    /**
     * Check chars allowed.
     */
    protected function checkCharsAllowed() {
        // Checking if all chars are allowed
        $c = strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            if (array_search($this->text[$i], $this->keys) === false) {
                throw new BCGParseException('ean13', 'The character \'' . $this->text[$i] . '\' is not allowed.');
            }
        }
    }

    /**
     * Check correct length.
     */
    protected function checkCorrectLength() {
        // If we have 13 chars, just flush the last one without throwing anything
        $c = strlen($this->text);
        if ($c === 13) {
            $this->text = substr($this->text, 0, 12);
        } elseif ($c !== 12) {
            throw new BCGParseException('ean13', 'Must contain 12 digits, the 13th digit is automatically added.');
        }
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
     * Draws the bars
     *
     * @param resource $im
     */
    protected function drawBars($im) {
        // Checksum
        $this->calculateChecksum();
        $temp_text = $this->text . $this->keys[$this->checksumValue];

        // Starting Code
        $this->drawChar($im, '000', true);

        // Draw Second Code
        $this->drawChar($im, $this->findCode($temp_text[1]), false);

        // Draw Manufacturer Code
        for ($i = 0; $i < 5; $i++) {
            $this->drawChar($im, self::inverse($this->findCode($temp_text[$i + 2]), $this->codeParity[(int)$temp_text[0]][$i]), false);
        }

        // Draw Center Guard Bar
        $this->drawChar($im, '00000', false);

        // Draw Product Code
        for ($i = 7; $i < 13; $i++) {
            $this->drawChar($im, $this->findCode($temp_text[$i]), true);
        }

        // Draw Right Guard Bar
        $this->drawChar($im, '000', true);
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

        // Center Guard Bar
        $this->positionX += 44;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);
        $this->positionX += 2;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);

        // Last Bars
        $this->positionX += 44;
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