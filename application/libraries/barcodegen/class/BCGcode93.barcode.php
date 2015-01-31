<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - Code 93
 *
 * !! Warning !!
 * If you display the checksum on the barcode, you may obtain
 * some garbage since some characters are not displayable.
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGBarcode1D.php');

class BCGcode93 extends BCGBarcode1D {
    const EXTENDED_1 = 43;
    const EXTENDED_2 = 44;
    const EXTENDED_3 = 45;
    const EXTENDED_4 = 46;

    private $starting, $ending;
    private $indcheck, $data;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->starting = $this->ending = 47; /* * */
        $this->keys = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%', '($)', '(%)', '(/)', '(+)', '(*)');
        $this->code = array(
            '020001',   /* 0 */
            '000102',   /* 1 */
            '000201',   /* 2 */
            '000300',   /* 3 */
            '010002',   /* 4 */
            '010101',   /* 5 */
            '010200',   /* 6 */
            '000003',   /* 7 */
            '020100',   /* 8 */
            '030000',   /* 9 */
            '100002',   /* A */
            '100101',   /* B */
            '100200',   /* C */
            '110001',   /* D */
            '110100',   /* E */
            '120000',   /* F */
            '001002',   /* G */
            '001101',   /* H */
            '001200',   /* I */
            '011001',   /* J */
            '021000',   /* K */
            '000012',   /* L */
            '000111',   /* M */
            '000210',   /* N */
            '010011',   /* O */
            '020010',   /* P */
            '101001',   /* Q */
            '101100',   /* R */
            '100011',   /* S */
            '100110',   /* T */
            '110010',   /* U */
            '111000',   /* V */
            '001011',   /* W */
            '001110',   /* X */
            '011010',   /* Y */
            '012000',   /* Z */
            '010020',   /* - */
            '200001',   /* . */
            '200100',   /*   */
            '210000',   /* $ */
            '001020',   /* / */
            '002010',   /* + */
            '100020',   /* % */
            '010110',   /*($)*/
            '201000',   /*(%)*/
            '200010',   /*(/)*/
            '011100',   /*(+)*/
            '000030'    /*(*)*/
        );
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
                    throw new BCGParseException('code93', 'The character \'' . $this->text[$i] . '\' is not allowed.');
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

        // Checksum
        $c = count($this->checksumValue);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->code[$this->checksumValue[$i]], true);
        }

        // Ending *
        $this->drawChar($im, $this->code[$this->ending], true);

        // Draw a Final Bar
        $this->drawChar($im, '0', true);
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
        $startlength = 9;
        $textlength = 9 * count($this->data);
        $checksumlength = 2 * 9;
        $endlength = 9 + 1; // + final bar

        $w += $startlength + $textlength + $checksumlength + $endlength;
        $h += $this->thickness;
        return parent::getDimension($w, $h);
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        $c = count($this->data);
        if ($c === 0) {
            throw new BCGParseException('code93', 'No data has been entered.');
        }

        parent::validate();
    }

    /**
     * Overloaded method to calculate checksum.
     */
    protected function calculateChecksum() {
        // Checksum
        // First CheckSUM "C"
        // The "C" checksum character is the modulo 47 remainder of the sum of the weighted
        // value of the data characters. The weighting value starts at "1" for the right-most
        // data character, 2 for the second to last, 3 for the third-to-last, and so on up to 20.
        // After 20, the sequence wraps around back to 1.

        // Second CheckSUM "K"
        // Same as CheckSUM "C" but we count the CheckSum "C" at the end
        // After 15, the sequence wraps around back to 1.
        $sequence_multiplier = array(20, 15);
        $this->checksumValue = array();
        $indcheck = $this->indcheck;
        for ($z = 0; $z < 2; $z++) {
            $checksum = 0;
            for ($i = count($indcheck), $j = 0; $i > 0; $i--, $j++) {
                $multiplier = $i % $sequence_multiplier[$z];
                if ($multiplier === 0) {
                    $multiplier = $sequence_multiplier[$z];
                }

                $checksum += $indcheck[$j] * $multiplier;
            }

            $this->checksumValue[$z] = $checksum % 47;
            $indcheck[] = $this->checksumValue[$z];
        }
    }

    /**
     * Overloaded method to display the checksum.
     */
    protected function processChecksum() {
        if ($this->checksumValue === false) { // Calculate the checksum only once
            $this->calculateChecksum();
        }

        if ($this->checksumValue !== false) {
            $ret = '';
            $c = count($this->checksumValue);
            for ($i = 0; $i < $c; $i++) {
                $ret .= $this->keys[$this->checksumValue[$i]];
            }

            return $ret;
        }

        return false;
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