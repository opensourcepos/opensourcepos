<?php
/**
 *--------------------------------------------------------------------
 *
 * Holds all type of barcodes for 1D generation
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGArgumentException.php');
include_once('BCGBarcode.php');
include_once('BCGFontPhp.php');
include_once('BCGFontFile.php');
include_once('BCGLabel.php');

abstract class BCGBarcode1D extends BCGBarcode {
    const SIZE_SPACING_FONT = 5;

    const AUTO_LABEL = '##!!AUTO_LABEL!!##';

    protected $thickness;       // int
    protected $keys, $code;     // string[]
    protected $positionX;       // int
    protected $font;            // BCGFont
    protected $text;            // string
    protected $checksumValue;   // int or int[]
    protected $displayChecksum; // bool
    protected $label;           // string
    protected $defaultLabel;    // BCGLabel

    /**
     * Constructor.
     */
    protected function __construct() {
        parent::__construct();

        $this->setThickness(30);

        $this->defaultLabel = new BCGLabel();
        $this->defaultLabel->setPosition(BCGLabel::POSITION_BOTTOM);
        $this->setLabel(self::AUTO_LABEL);
        $this->setFont(new BCGFontPhp(5));

        $this->text = '';
        $this->checksumValue = false;
        $this->positionX = 0;
    }

    /**
     * Gets the thickness.
     *
     * @return int
     */
    public function getThickness() {
        return $this->thickness;
    }

    /**
     * Sets the thickness.
     *
     * @param int $thickness
     */
    public function setThickness($thickness) {
        $thickness = intval($thickness);
        if ($thickness <= 0) {
            throw new BCGArgumentException('The thickness must be larger than 0.', 'thickness');
        }

        $this->thickness = $thickness;
    }

    /**
     * Gets the label.
     * If the label was set to BCGBarcode1D::AUTO_LABEL, the label will display the value from the text parsed.
     *
     * @return string
     */
    public function getLabel() {
        $label = $this->label;
        if ($this->label === self::AUTO_LABEL) {
            $label = $this->text;
            if ($this->displayChecksum === true && ($checksum = $this->processChecksum()) !== false) {
                $label .= $checksum;
            }
        }

        return $label;
    }

    /**
     * Sets the label.
     * You can use BCGBarcode::AUTO_LABEL to have the label automatically written based on the parsed text.
     *
     * @param string $label
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * Gets the font.
     *
     * @return BCGFont
     */
    public function getFont() {
        return $this->font;
    }

    /**
     * Sets the font.
     *
     * @param mixed $font BCGFont or int
     */
    public function setFont($font) {
        if (is_int($font)) {
            if ($font === 0) {
                $font = null;
            } else {
                $font = new BCGFontPhp($font);
            }
        }

        $this->font = $font;
    }

    /**
     * Parses the text before displaying it.
     *
     * @param mixed $text
     */
    public function parse($text) {
        $this->text = $text;
        $this->checksumValue = false; // Reset checksumValue
        $this->validate();

        parent::parse($text);

        $this->addDefaultLabel();
    }

    /**
     * Gets the checksum of a Barcode.
     * If no checksum is available, return FALSE.
     *
     * @return string
     */
    public function getChecksum() {
        return $this->processChecksum();
    }

    /**
     * Sets if the checksum is displayed with the label or not.
     * The checksum must be activated in some case to make this variable effective.
     *
     * @param boolean $displayChecksum
     */
    public function setDisplayChecksum($displayChecksum) {
        $this->displayChecksum = (bool)$displayChecksum;
    }

    /**
     * Adds the default label.
     */
    protected function addDefaultLabel() {
        $label = $this->getLabel();
        $font = $this->font;
        if ($label !== null && $label !== '' && $font !== null && $this->defaultLabel !== null) {
            $this->defaultLabel->setText($label);
            $this->defaultLabel->setFont($font);
            $this->addLabel($this->defaultLabel);
        }
    }

    /**
     * Validates the input
     */
    protected function validate() {
        // No validation in the abstract class.
    }

    /**
     * Returns the index in $keys (useful for checksum).
     *
     * @param mixed $var
     * @return mixed
     */
    protected function findIndex($var) {
        return array_search($var, $this->keys);
    }

    /**
     * Returns the code of the char (useful for drawing bars).
     *
     * @param mixed $var
     * @return string
     */
    protected function findCode($var) {
        return $this->code[$this->findIndex($var)];
    }

    /**
     * Draws all chars thanks to $code. If $startBar is true, the line begins by a space.
     * If $startBar is false, the line begins by a bar.
     *
     * @param resource $im
     * @param string $code
     * @param boolean $startBar
     */
    protected function drawChar($im, $code, $startBar = true) {
        $colors = array(BCGBarcode::COLOR_FG, BCGBarcode::COLOR_BG);
        $currentColor = $startBar ? 0 : 1;
        $c = strlen($code);
        for ($i = 0; $i < $c; $i++) {
            for ($j = 0; $j < intval($code[$i]) + 1; $j++) {
                $this->drawSingleBar($im, $colors[$currentColor]);
                $this->nextX();
            }

            $currentColor = ($currentColor + 1) % 2;
        }
    }

    /**
     * Draws a Bar of $color depending of the resolution.
     *
     * @param resource $img
     * @param int $color
     */
    protected function drawSingleBar($im, $color) {
        $this->drawFilledRectangle($im, $this->positionX, 0, $this->positionX, $this->thickness - 1, $color);
    }

    /**
     * Moving the pointer right to write a bar.
     */
    protected function nextX() {
        $this->positionX++;
    }

    /**
     * Method that saves FALSE into the checksumValue. This means no checksum
     * but this method should be overriden when needed.
     */
    protected function calculateChecksum() {
        $this->checksumValue = false;
    }

    /**
     * Returns FALSE because there is no checksum. This method should be
     * overriden to return correctly the checksum in string with checksumValue.
     *
     * @return string
     */
    protected function processChecksum() {
        return false;
    }
}
?>