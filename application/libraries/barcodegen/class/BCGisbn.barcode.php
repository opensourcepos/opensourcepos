<?php
/**
 *--------------------------------------------------------------------
 *
 * Sub-Class - ISBN-10 and ISBN-13
 *
 * You can provide an ISBN with 10 digits with or without the checksum.
 * You can provide an ISBN with 13 digits with or without the checksum.
 * Calculate the ISBN based on the EAN-13 encoding.
 *
 * The checksum is always displayed.
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGArgumentException.php');
include_once('BCGean13.barcode.php');

class BCGisbn extends BCGean13 {
    const GS1_AUTO = 0;
    const GS1_PREFIX978 = 1;
    const GS1_PREFIX979 = 2;

    private $gs1;

    /**
     * Constructor.
     *
     * @param int $gs1
     */
    public function __construct($gs1 = self::GS1_AUTO) {
        parent::__construct();
        $this->setGS1($gs1);
    }

    /**
     * Adds the default label.
     */
    protected function addDefaultLabel() {
        if ($this->isDefaultEanLabelEnabled()) {
            $isbn = $this->createISBNText();
            $font = $this->font;

            $topLabel = new BCGLabel($isbn, $font, BCGLabel::POSITION_TOP, BCGLabel::ALIGN_CENTER);

            $this->addLabel($topLabel);
        }

        parent::addDefaultLabel();
    }

    /**
     * Sets the first numbers of the barcode.
     *  - GS1_AUTO: Adds 978 before the code
     *  - GS1_PREFIX978: Adds 978 before the code
     *  - GS1_PREFIX979: Adds 979 before the code
     *
     * @param int $gs1
     */
    public function setGS1($gs1) {
        $gs1 = (int)$gs1;
        if ($gs1 !== self::GS1_AUTO && $gs1 !== self::GS1_PREFIX978 && $gs1 !== self::GS1_PREFIX979) {
            throw new BCGArgumentException('The GS1 argument must be BCGisbn::GS1_AUTO, BCGisbn::GS1_PREFIX978, or BCGisbn::GS1_PREFIX979', 'gs1');
        }

        $this->gs1 = $gs1;
    }

    /**
     * Check chars allowed.
     */
    protected function checkCharsAllowed() {
        $c = strlen($this->text);

        // Special case, if we have 10 digits, the last one can be X
        if ($c === 10) {
            if (array_search($this->text[9], $this->keys) === false && $this->text[9] !== 'X') {
                throw new BCGParseException('isbn', 'The character \'' . $this->text[9] . '\' is not allowed.');
            }

            // Drop the last char
            $this->text = substr($this->text, 0, 9);
        }

        return parent::checkCharsAllowed();
    }

    /**
     * Check correct length.
     */
    protected function checkCorrectLength() {
        $c = strlen($this->text);

        // If we have 13 chars just flush the last one
        if ($c === 13) {
            $this->text = substr($this->text, 0, 12);
        } elseif ($c === 9 || $c === 10) {
            if ($c === 10) {
                // Before dropping it, we check if it's legal
                if (array_search($this->text[9], $this->keys) === false && $this->text[9] !== 'X') {
                    throw new BCGParseException('isbn', 'The character \'' . $this->text[9] . '\' is not allowed.');
                }

                $this->text = substr($this->text, 0, 9);
            }

            if ($this->gs1 === self::GS1_AUTO || $this->gs1 === self::GS1_PREFIX978) {
                $this->text = '978' . $this->text;
            } elseif ($this->gs1 === self::GS1_PREFIX979) {
                $this->text = '979' . $this->text;
            }
        } elseif ($c !== 12) {
            throw new BCGParseException('isbn', 'The code parsed must be 9, 10, 12, or 13 digits long.');
        }
    }

    /**
     * Creates the ISBN text.
     *
     * @return string
     */
    private function createISBNText() {
        $isbn = '';
        if (!empty($this->text)) {
            // We try to create the ISBN Text... the hyphen really depends the ISBN agency.
            // We just put one before the checksum and one after the GS1 if present.
            $c = strlen($this->text);
            if ($c === 12 || $c === 13) {
                // If we have 13 characters now, just transform it temporarily to find the checksum...
                // Further in the code we take care of that anyway.
                $lastCharacter = '';
                if ($c === 13) {
                    $lastCharacter = $this->text[12];
                    $this->text = substr($this->text, 0, 12);
                }

                $checksum = $this->processChecksum();
                $isbn = 'ISBN ' . substr($this->text, 0, 3) . '-' . substr($this->text, 3, 9) . '-' . $checksum;

                // Put the last character back
                if ($c === 13) {
                    $this->text .= $lastCharacter;
                }
            } elseif ($c === 9 || $c === 10) {
                $checksum = 0;
                for ($i = 10; $i >= 2; $i--) {
                    $checksum += $this->text[10 - $i] * $i;
                }

                $checksum = 11 - $checksum % 11;
                if ($checksum === 10) {
                    $checksum = 'X'; // Changing type
                }

                $isbn = 'ISBN ' . substr($this->text, 0, 9) . '-' . $checksum;
            }
        }

        return $isbn;
    }
}
?>