<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - Code 39 Extended
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGcode39.barcode.php');

class BCGcode39extended extends BCGcode39 {
    const EXTENDED_1 = 39;
    const EXTENDED_2 = 40;
    const EXTENDED_3 = 41;
    const EXTENDED_4 = 42;

    protected $indcheck, $data;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        // We just put parenthesis around special characters.
        $this->keys[self::EXTENDED_1] = '($)';
        $this->keys[self::EXTENDED_2] = '(/)';
        $this->keys[self::EXTENDED_3] = '(+)';
        $this->keys[self::EXTENDED_4] = '(%)';
    }

    /**
     * Parses the text before displaying it.
     *
     * @param mixed $text
     */
    public function parse($text) {
        $this->text = $text;

        $data = array();
        $indcheck = array();

        $c = strlen($this->text);
        for ($i = 0; $i < $c; $i++) {
            $pos = array_search($this->text[$i], $this->keys);
            if ($pos === false) {
                // Search in extended?
                $extended = self::getExtendedVersion($this->text[$i]);
                if ($extended === false) {
                    throw new BCGParseException('code39extended', 'The character \'' . $this->text[$i] . '\' is not allowed.');
                } else {
                    $extc = strlen($extended);
                    for ($j = 0; $j < $extc; $j++) {
                        $v = $extended[$j];
                        if ($v === '$') {
                            $indcheck[] = self::EXTENDED_1;
                            $data[] = $this->code[self::EXTENDED_1];
                        } elseif ($v === '%') {
                            $indcheck[] = self::EXTENDED_2;
                            $data[] = $this->code[self::EXTENDED_2];
                        } elseif ($v === '/') {
                            $indcheck[] = self::EXTENDED_3;
                            $data[] = $this->code[self::EXTENDED_3];
                        } elseif ($v === '+') {
                            $indcheck[] = self::EXTENDED_4;
                            $data[] = $this->code[self::EXTENDED_4];
                        } else {
                            $pos2 = array_search($v, $this->keys);
                            $indcheck[] = $pos2;
                            $data[] = $this->code[$pos2];
                        }
                    }
                }
            } else {
                $indcheck[] = $pos;
                $data[] = $this->code[$pos];
            }
        }

        $this->setData(array($indcheck, $data));
        $this->addDefaultLabel();
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {
        // Starting *
        $this->drawChar($im, $this->code[$this->starting], true);
        $c = count($this->data);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->data[$i], true);
        }

        // Checksum (rarely used)
        if ($this->checksum === true) {
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
        $textlength = 13 * count($this->data);
        $startlength = 13;
        $checksumlength = 0;
        if ($this->checksum === true) {
            $checksumlength = 13;
        }

        $endlength = 13;

        $w += $startlength + $textlength + $checksumlength + $endlength;
        $h += $this->thickness;
        return BCGBarcode1D::getDimension($w, $h);
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        $c = count($this->data);
        if ($c === 0) {
            throw new BCGParseException('code39extended', 'No data has been entered.');
        }

        parent::validate();
    }

    /**
     * Overloaded method to calculate checksum.
     */
    protected function calculateChecksum() {
        $this->checksumValue = 0;
        $c = count($this->indcheck);
        for ($i = 0; $i < $c; $i++) {
            $this->checksumValue += $this->indcheck[$i];
        }

        $this->checksumValue = $this->checksumValue % 43;
    }

    /**
     * Saves data into the classes.
     *
     * This method will save data, calculate real column number
     * (if -1 was selected), the real error level (if -1 was
     * selected)... It will add Padding to the end and generate
     * the error codes.
     *
     * @param array $data
     */
    private function setData($data) {
        $this->indcheck = $data[0];
        $this->data = $data[1];
        $this->calculateChecksum();
    }

    /**
     * Returns the extended reprensentation of the character.
     *
     * @param string $char
     * @return string
     */
    private static function getExtendedVersion($char) {
        $o = ord($char);
        if ($o === 0) {
            return '%U';
        } elseif ($o >= 1 && $o <= 26) {
            return '$' . chr($o + 64);
        } elseif (($o >= 33 && $o <= 44) || $o === 47 || $o === 48) {
            return '/' . chr($o + 32);
        } elseif ($o >= 97 && $o <= 122) {
            return '+' . chr($o - 32);
        } elseif ($o >= 27 && $o <= 31) {
            return '%' . chr($o + 38);
        } elseif ($o >= 59 && $o <= 63) {
            return '%' . chr($o + 11);
        } elseif ($o >= 91 && $o <= 95) {
            return '%' . chr($o - 16);
        } elseif ($o >= 123 && $o <= 127) {
            return '%' . chr($o - 43);
        } elseif ($o === 64) {
            return '%V';
        } elseif ($o === 96) {
            return '%W';
        } elseif ($o > 127) {
            return false;
        } else {
            return $char;
        }
    }
}
?>