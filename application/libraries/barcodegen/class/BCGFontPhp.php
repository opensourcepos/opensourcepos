<?php
/**
 *--------------------------------------------------------------------
 *
 * Holds font for PHP.
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGFont.php');
include_once('BCGColor.php');

class BCGFontPhp implements BCGFont {
    private $font;
    private $text;
    private $rotationAngle;
    private $backgroundColor;
    private $foregroundColor;

    /**
     * Constructor.
     *
     * @param int $font
     */
    public function __construct($font) {
        $this->font = max(0, intval($font));
        $this->backgroundColor = new BCGColor('white');
        $this->foregroundColor = new BCGColor('black');
        $this->setRotationAngle(0);
    }

    /**
     * Gets the text associated to the font.
     *
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Sets the text associated to the font.
     *
     * @param string text
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * Gets the rotation in degree.
     *
     * @return int
     */
    public function getRotationAngle() {
        return (360 - $this->rotationAngle) % 360;
    }

    /**
     * Sets the rotation in degree.
     *
     * @param int
     */
    public function setRotationAngle($rotationAngle) {
        $this->rotationAngle = (int)$rotationAngle;
        if ($this->rotationAngle !== 90 && $this->rotationAngle !== 180 && $this->rotationAngle !== 270) {
            $this->rotationAngle = 0;
        }

        $this->rotationAngle = (360 - $this->rotationAngle) % 360;
    }

    /**
     * Gets the background color.
     *
     * @return BCGColor
     */
    public function getBackgroundColor() {
        return $this->backgroundColor;
    }

    /**
     * Sets the background color.
     *
     * @param BCGColor $backgroundColor
     */
    public function setBackgroundColor($backgroundColor) {
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * Gets the foreground color.
     *
     * @return BCGColor
     */
    public function getForegroundColor() {
        return $this->foregroundColor;
    }

    /**
     * Sets the foreground color.
     *
     * @param BCGColor $foregroundColor
     */
    public function setForegroundColor($foregroundColor) {
        $this->foregroundColor = $foregroundColor;
    }

    /**
     * Returns the width and height that the text takes to be written.
     *
     * @return int[]
     */
    public function getDimension() {
        $w = imagefontwidth($this->font) * strlen($this->text);
        $h = imagefontheight($this->font);

        $rotationAngle = $this->getRotationAngle();
        if ($rotationAngle === 90 || $rotationAngle === 270) {
            return array($h, $w);
        } else {
            return array($w, $h);
        }
    }

    /**
     * Draws the text on the image at a specific position.
     * $x and $y represent the left bottom corner.
     *
     * @param resource $im
     * @param int $x
     * @param int $y
     */
    public function draw($im, $x, $y) {
        if ($this->getRotationAngle() !== 0) {
            if (!function_exists('imagerotate')) {
                throw new BCGDrawException('The method imagerotate doesn\'t exist on your server. Do not use any rotation.');
            }

            $w = imagefontwidth($this->font) * strlen($this->text);
            $h = imagefontheight($this->font);
            $gd = imagecreatetruecolor($w, $h);
            imagefilledrectangle($gd, 0, 0, $w - 1, $h - 1, $this->backgroundColor->allocate($gd));
            imagestring($gd, $this->font, 0, 0, $this->text, $this->foregroundColor->allocate($gd));
            $gd = imagerotate($gd, $this->rotationAngle, 0);
            imagecopy($im, $gd, $x, $y, 0, 0, imagesx($gd), imagesy($gd));
        } else {
            imagestring($im, $this->font, $x, $y, $this->text, $this->foregroundColor->allocate($im));
        }
    }
}
?>