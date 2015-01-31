<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - UPC-A
 *
 * UPC-A contains
 *    - 2 system digits (1 not provided, a 0 is added automatically)
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
include_once('BCGean13.barcode.php');
include_once('BCGLabel.php');

class BCGupca extends BCGean13 {
    protected $labelRight = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        // The following code is exactly the same as EAN13. We just add a 0 in front of the code !
        $this->text = '0' . $this->text; // We will remove it at the end... don't worry

        parent::draw($im);

        // We remove the 0 in front, as we said :)
        $this->text = substr($this->text, 1);
    }

    /**
     * Draws the extended bars on the image.
     *
     * @param resource $im
     * @param int $plus
     */
    protected function drawExtendedBars($im, $plus) {
        $temp_text = $this->text . $this->keys[$this->checksumValue];
        $rememberX = $this->positionX;
        $rememberH = $this->thickness;

        // We increase the bars
        // First 2 Bars
        $this->thickness = $this->thickness + intval($plus / $this->scale);
        $this->positionX = 0;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);
        $this->positionX += 2;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);

        // Attemping to increase the 2 following bars
        $this->positionX += 1;
        $temp_value = $this->findCode($temp_text[1]);
        $this->drawChar($im, $temp_value, false);

        // Center Guard Bar
        $this->positionX += 36;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);
        $this->positionX += 2;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);

        // Attemping to increase the 2 last bars
        $this->positionX += 37;
        $temp_value = $this->findCode($temp_text[12]);
        $this->drawChar($im, $temp_value, true);

        // Completly last bars
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);
        $this->positionX += 2;
        $this->drawSingleBar($im, BCGBarcode::COLOR_FG);

        $this->positionX = $rememberX;
        $this->thickness = $rememberH;
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

            $this->labelCenter1 = new BCGLabel(substr($label, 1, 5), $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
            $labelCenter1Dimension = $this->labelCenter1->getDimension();
            $this->labelCenter1->setOffset(($this->scale * 44 - $labelCenter1Dimension[0]) / 2 + $this->scale * 6);

            $this->labelCenter2 = new BCGLabel(substr($label, 6, 5), $font, BCGLabel::POSITION_BOTTOM, BCGLabel::ALIGN_LEFT);
            $this->labelCenter2->setOffset(($this->scale * 44 - $labelCenter1Dimension[0]) / 2 + $this->scale * 45);

            $this->labelRight = new BCGLabel($this->keys[$this->checksumValue], $font, BCGLabel::POSITION_RIGHT, BCGLabel::ALIGN_BOTTOM);
            $this->labelRight->setSpacing(4 * $this->scale);

            if ($this->alignLabel) {
                $labelDimension = $this->labelCenter1->getDimension();
                $this->labelLeft->setOffset($labelDimension[1]);
                $this->labelRight->setOffset($labelDimension[1]);
            } else {
                $labelDimension = $this->labelLeft->getDimension();
                $this->labelLeft->setOffset($labelDimension[1] / 2);
                $labelDimension = $this->labelLeft->getDimension();
                $this->labelRight->setOffset($labelDimension[1] / 2);
            }

            $this->addLabel($this->labelLeft);
            $this->addLabel($this->labelCenter1);
            $this->addLabel($this->labelCenter2);
            $this->addLabel($this->labelRight);
        }
    }

    /**
     * Check correct length.
     */
    protected function checkCorrectLength() {
        // If we have 12 chars, just flush the last one without throwing anything
        $c = strlen($this->text);
        if ($c === 12) {
            $this->text = substr($this->text, 0, 11);
        } elseif ($c !== 11) {
            throw new BCGParseException('upca', 'Must contain 11 digits, the 12th digit is automatically added.');
        }
    }
}
?>