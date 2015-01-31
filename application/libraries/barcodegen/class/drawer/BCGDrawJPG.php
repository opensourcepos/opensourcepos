<?php
/**
 *--------------------------------------------------------------------
 *
 * Image Class to draw JPG images with possibility to set DPI
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGDraw.php');

if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data) {
        $f = @fopen($filename, 'w');
        if (!$f) {
            return false;
        } else {
            $bytes = fwrite($f, $data);
            fclose($f);
            return $bytes;
        }
    }
}

class BCGDrawJPG extends BCGDraw {
    private $dpi;
    private $quality;

    /**
     * Constructor.
     *
     * @param resource $im
     */
    public function __construct($im) {
        parent::__construct($im);
    }

    /**
     * Sets the DPI.
     *
     * @param int $dpi
     */
    public function setDPI($dpi) {
        if(is_int($dpi)) {
            $this->dpi = max(1, $dpi);
        } else {
            $this->dpi = null;
        }
    }

    /**
     * Sets the quality of the JPG.
     *
     * @param int $quality
     */
    public function setQuality($quality) {
        $this->quality = $quality;
    }

    /**
     * Draws the JPG on the screen or in a file.
     */
    public function draw() {
        ob_start();
        imagejpeg($this->im, null, $this->quality);
        $bin = ob_get_contents();
        ob_end_clean();

        $this->setInternalProperties($bin);

        if (empty($this->filename)) {
            echo $bin;
        } else {
            file_put_contents($this->filename, $bin);
        }
    }

    private function setInternalProperties(&$bin) {
        $this->internalSetDPI($bin);
        $this->internalSetC($bin);
    }

    private function internalSetDPI(&$bin) {
        if ($this->dpi !== null) {
            $bin = substr_replace($bin, pack("Cnn", 0x01, $this->dpi, $this->dpi), 13, 5);
        }
    }

    private function internalSetC(&$bin) {
        if(strcmp(substr($bin, 0, 4), pack('H*', 'FFD8FFE0')) === 0) {
            $offset = 4 + (ord($bin[4]) << 8 | ord($bin[5]));
            $firstPart = substr($bin, 0, $offset);
            $secondPart = substr($bin, $offset);
            $cr = pack('H*', 'FFFE004447656E657261746564207769746820426172636F64652047656E657261746F7220666F722050485020687474703A2F2F7777772E626172636F64657068702E636F6D');
            $bin = $firstPart;
            $bin .= $cr;
            $bin .= $secondPart;
        }
    }
}
?>