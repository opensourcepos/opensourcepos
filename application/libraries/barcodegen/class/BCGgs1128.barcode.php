<?php
/**
 *--------------------------------------------------------------------
 *
 * Calculate the GS1-128 based on the Code-128 encoding.
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGParseException.php');
include_once('BCGcode128.barcode.php');

class BCGgs1128 extends BCGcode128 {
    const KIND_OF_DATA = 0;
    const MINLENGTH = 1;
    const MAXLENGTH = 2;
    const CHECKSUM = 3;
    const NUMERIC = 0;
    const ALPHA_NUMERIC = 1;
    const DATE_YYMMDD = 2;
    const ID = 0;
    const CONTENT = 1;
    const MAX_ID_FORMATED = 6;
    const MAX_ID_NOT_FORMATED = 4;
    const MAX_GS1128_CHARS = 48;

    private $strictMode;
    private $allowsUnknownIdentifier;
    private $noLengthLimit;
    private $identifiersId = array();
    private $identifiersContent = array();
    private $identifiersAi = array();

    /**
     * Constructors
     *
     * @param char $start
     */
    public function __construct($start = null) {
        if ($start === null) {
            $start = 'C';
        }

        parent::__construct($start);

        /* Application Identifiers (AIs) */
        /*
        array ( KIND_OF_DATA , MINLENGTH , MAXLENGTH , CHECKSUM )
        KIND_OF_DATA:        NUMERIC , ALPHA_NUMERIC or DATE_YYMMDD
        CHECKSUM:            bool (true / false)
        */
        $this->identifiersAi = array(
        '00'    =>    array(self::NUMERIC,          18, 18, true),
        '01'    =>    array(self::NUMERIC,          14, 14, true),
        '02'    =>    array(self::NUMERIC,          14, 14, true),
        '10'    =>    array(self::ALPHA_NUMERIC,    1,  20, false),
        '11'    =>    array(self::DATE_YYMMDD,      6,  6,  false),
        '12'    =>    array(self::DATE_YYMMDD,      6,  6,  false),
        '13'    =>    array(self::DATE_YYMMDD,      6,  6,  false),
        '15'    =>    array(self::DATE_YYMMDD,      6,  6,  false),
        '17'    =>    array(self::DATE_YYMMDD,      6,  6,  false),
        '20'    =>    array(self::NUMERIC,          2,  2,  false),
        '21'    =>    array(self::ALPHA_NUMERIC,    1,  20, false),
        '240'   =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '241'   =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '250'   =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '251'   =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '253'   =>    array(self::NUMERIC,          14, 30, false),
        '30'    =>    array(self::NUMERIC,          1,  8,  false),
        '310y'  =>    array(self::NUMERIC,          6,  6,  false),
        '311y'  =>    array(self::NUMERIC,          6,  6,  false),
        '312y'  =>    array(self::NUMERIC,          6,  6,  false),
        '313y'  =>    array(self::NUMERIC,          6,  6,  false),
        '314y'  =>    array(self::NUMERIC,          6,  6,  false),
        '315y'  =>    array(self::NUMERIC,          6,  6,  false),
        '316y'  =>    array(self::NUMERIC,          6,  6,  false),
        '320y'  =>    array(self::NUMERIC,          6,  6,  false),
        '321y'  =>    array(self::NUMERIC,          6,  6,  false),
        '322y'  =>    array(self::NUMERIC,          6,  6,  false),
        '323y'  =>    array(self::NUMERIC,          6,  6,  false),
        '324y'  =>    array(self::NUMERIC,          6,  6,  false),
        '325y'  =>    array(self::NUMERIC,          6,  6,  false),
        '326y'  =>    array(self::NUMERIC,          6,  6,  false),
        '327y'  =>    array(self::NUMERIC,          6,  6,  false),
        '328y'  =>    array(self::NUMERIC,          6,  6,  false),
        '329y'  =>    array(self::NUMERIC,          6,  6,  false),
        '330y'  =>    array(self::NUMERIC,          6,  6,  false),
        '331y'  =>    array(self::NUMERIC,          6,  6,  false),
        '332y'  =>    array(self::NUMERIC,          6,  6,  false),
        '333y'  =>    array(self::NUMERIC,          6,  6,  false),
        '334y'  =>    array(self::NUMERIC,          6,  6,  false),
        '335y'  =>    array(self::NUMERIC,          6,  6,  false),
        '336y'  =>    array(self::NUMERIC,          6,  6,  false),
        '337y'  =>    array(self::NUMERIC,          6,  6,  false),
        '340y'  =>    array(self::NUMERIC,          6,  6,  false),
        '341y'  =>    array(self::NUMERIC,          6,  6,  false),
        '342y'  =>    array(self::NUMERIC,          6,  6,  false),
        '343y'  =>    array(self::NUMERIC,          6,  6,  false),
        '344y'  =>    array(self::NUMERIC,          6,  6,  false),
        '345y'  =>    array(self::NUMERIC,          6,  6,  false),
        '346y'  =>    array(self::NUMERIC,          6,  6,  false),
        '347y'  =>    array(self::NUMERIC,          6,  6,  false),
        '348y'  =>    array(self::NUMERIC,          6,  6,  false),
        '349y'  =>    array(self::NUMERIC,          6,  6,  false),
        '350y'  =>    array(self::NUMERIC,          6,  6,  false),
        '351y'  =>    array(self::NUMERIC,          6,  6,  false),
        '352y'  =>    array(self::NUMERIC,          6,  6,  false),
        '353y'  =>    array(self::NUMERIC,          6,  6,  false),
        '354y'  =>    array(self::NUMERIC,          6,  6,  false),
        '355y'  =>    array(self::NUMERIC,          6,  6,  false),
        '356y'  =>    array(self::NUMERIC,          6,  6,  false),
        '357y'  =>    array(self::NUMERIC,          6,  6,  false),
        '360y'  =>    array(self::NUMERIC,          6,  6,  false),
        '361y'  =>    array(self::NUMERIC,          6,  6,  false),
        '362y'  =>    array(self::NUMERIC,          6,  6,  false),
        '363y'  =>    array(self::NUMERIC,          6,  6,  false),
        '364y'  =>    array(self::NUMERIC,          6,  6,  false),
        '365y'  =>    array(self::NUMERIC,          6,  6,  false),
        '366y'  =>    array(self::NUMERIC,          6,  6,  false),
        '367y'  =>    array(self::NUMERIC,          6,  6,  false),
        '368y'  =>    array(self::NUMERIC,          6,  6,  false),
        '369y'  =>    array(self::NUMERIC,          6,  6,  false),
        '37'    =>    array(self::NUMERIC,          1,  8,  false),
        '390y'  =>    array(self::NUMERIC,          1,  15, false),
        '391y'  =>    array(self::NUMERIC,          4,  18, false),
        '392y'  =>    array(self::NUMERIC,          1,  15, false),
        '393y'  =>    array(self::NUMERIC,          4,  18, false),
        '400'   =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '401'   =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '402'   =>    array(self::NUMERIC,          17, 17, false),
        '403'   =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '410'   =>    array(self::NUMERIC,          13, 13, true),
        '411'   =>    array(self::NUMERIC,          13, 13, true),
        '412'   =>    array(self::NUMERIC,          13, 13, true),
        '413'   =>    array(self::NUMERIC,          13, 13, true),
        '414'   =>    array(self::NUMERIC,          13, 13, true),
        '415'   =>    array(self::NUMERIC,          13, 13, true),
        '420'   =>    array(self::ALPHA_NUMERIC,    1,  20, false),
        '421'   =>    array(self::ALPHA_NUMERIC,    4,  12, false),
        '422'   =>    array(self::NUMERIC,          3,  3,  false),
        '8001'  =>    array(self::NUMERIC,          14, 14, false),
        '8002'  =>    array(self::ALPHA_NUMERIC,    1,  20, false),
        '8003'  =>    array(self::ALPHA_NUMERIC,    15, 30, false),
        '8004'  =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '8005'  =>    array(self::NUMERIC,          6,  6,  false),
        '8006'  =>    array(self::NUMERIC,          18, 18, false),
        '8007'  =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '8018'  =>    array(self::NUMERIC,          18, 18, false),
        '8020'  =>    array(self::ALPHA_NUMERIC,    1,  25, false),
        '8100'  =>    array(self::NUMERIC,          6,  6,  false),
        '8101'  =>    array(self::NUMERIC,          10, 10, false),
        '8102'  =>    array(self::NUMERIC,          2,  2,  false),
        '90'    =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '91'    =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '92'    =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '93'    =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '94'    =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '95'    =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '96'    =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '97'    =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '98'    =>    array(self::ALPHA_NUMERIC,    1,  30, false),
        '99'    =>    array(self::ALPHA_NUMERIC,    1,  30, false)
        );

        $this->setStrictMode(true);
        $this->setTilde(true);
        $this->setAllowsUnknownIdentifier(false);
        $this->setNoLengthLimit(false);
    }

    /**
     * Gets the content checksum for an identifier.
     * Do not pass the identifier code.
     *
     * @param string $content
     * @return int
     */
    public static function getAiContentChecksum($content) {
        return self::calculateChecksumMod10($content);
    }

    /**
     * Enables or disables the strict mode.
     *
     * @param bool $strictMode
     */
    public function setStrictMode($strictMode) {
        $this->strictMode = $strictMode;
    }

    /**
     * Gets if the strict mode is activated.
     *
     * @return bool
     */
    public function getStrictMode() {
        return $this->strictMode;
    }

    /**
     * Allows unknown identifiers.
     *
     * @param bool $allow
     */
    public function setAllowsUnknownIdentifier($allow) {
        $this->allowsUnknownIdentifier = (bool)$allow;
    }

    /**
     * Gets if unkmown identifiers are allowed.
     *
     * @return bool
     */
    public function getAllowsUnknownIdentifier() {
        return $this->allowsUnknownIdentifier;
    }

    /**
     * Removes the limit of 48 characters.
     *
     * @param bool $noLengthLimit
     */
    public function setNoLengthLimit($noLengthLimit) {
        $this->noLengthLimit = (bool)$noLengthLimit;
    }

    /**
     * Gets if the limit of 48 characters is removed.
     *
     * @return bool
     */
    public function getNoLengthLimit() {
        return $this->noLengthLimit;
    }

    /**
     * Parses Text.
     *
     * @param string $text
     */
    public function parse($text) {
        parent::parse($this->parseGs1128($text));
    }

    /**
     * Formats data for gs1-128.
     *
     * @return string
     */
    private function formatGs1128() {
        $formatedText = '~F1';
        $formatedLabel = '';
        $c = count($this->identifiersId);

        for ($i = 0; $i < $c; $i++) {
            if ($i > 0) {
                $formatedLabel .= ' ';
            }

            if ($this->identifiersId[$i] !== null) {
                $formatedLabel .= '(' . $this->identifiersId[$i] . ')';
            }

            $formatedText .= $this->identifiersId[$i];

            $formatedLabel .= $this->identifiersContent[$i];
            $formatedText .= $this->identifiersContent[$i];

            if (isset($this->identifiersAi[$this->identifiersId[$i]])) {
                $ai_data = $this->identifiersAi[$this->identifiersId[$i]];
            } elseif (isset($this->identifiersId[$i][3])) {
                $identifierWithVar = substr($this->identifiersId[$i], 0, -1) . 'y';
                $ai_data = isset($this->identifiersAi[$identifierWithVar]) ? $this->identifiersAi[$identifierWithVar] : null;
            } else {
                $ai_data = null;
            }

            /* We'll check if we need to add a ~F1 (<GS>) char */
            /* If we use the legacy mode, we always add a ~F1 (<GS>) char between AIs */
            if ($ai_data !== null) {
                if ((strlen($this->identifiersContent[$i]) < $ai_data[self::MAXLENGTH] && ($i + 1) !== $c) || (!$this->strictMode && ($i + 1) !== $c)) {
                    $formatedText .= '~F1';
                }
            } elseif ($this->allowsUnknownIdentifier && $this->identifiersId[$i] === null && ($i + 1) !== $c) {
                /* If this id is unknown, we add a ~F1 (<GS>) char */
                $formatedText .= '~F1';
            }
        }

        if ($this->noLengthLimit === false && (strlen(str_replace('~F1', chr(29), $formatedText)) - 1) > self::MAX_GS1128_CHARS) {
            throw new BCGParseException('gs1128', 'The barcode can\'t contain more than ' . self::MAX_GS1128_CHARS . ' characters.');
        }

        $this->label = $formatedLabel;
        return $formatedText;
    }

    /**
     * Parses the text to gs1-128.
     *
     * @param mixed $text
     * @return mixed
     */
    private function parseGs1128($text) {
        /* We format correctly what the user gives */
        if (is_array($text)) {
            $formatArray = array();
            foreach ($text as $content) {
                if (is_array($content)) { /* double array */
                    if (count($content) === 2) {
                        if (is_array($content[self::ID]) || is_array($content[self::CONTENT])) {
                            throw new BCGParseException('gs1128', 'Double arrays can\'t contain arrays.');
                        } else {
                            $formatArray[] = '(' . $content[self::ID] . ')' . $content[self::CONTENT];
                        }
                    } else {
                        throw new BCGParseException('gs1128', 'Double arrays must contain 2 values.');
                    }
                } else { /* simple array */
                    $formatArray[] = $content;
                }
            }

            unset($text);
            $text = $formatArray;
        } else { /* string */
            $text = array($text);
        }

        $textCount = count($text);
        for ($cmpt = 0; $cmpt < $textCount; $cmpt++) {
            /* We parse the content of the array */
            if (!$this->parseContent($text[$cmpt])) {
                return;
            }
        }

        return $this->formatGs1128();
    }

    /**
     * Splits the id and the content for each application identifiers (AIs).
     *
     * @param string $text
     * @param int $cmpt
     * @return bool
     */
    private function parseContent($text) {
        /* $yAlreadySet has 3 states: */
        /* null: There is no variable in the ID; true: the variable is already set; false: the variable is not set yet; */
        $content = null;
        $yAlreadySet = null;
        $realNameId = null;
        $separatorsFound = 0;
        $checksumAdded = 0;
        $decimalPointRemoved = 0;
        $toParse = str_replace('~F1', chr(29), $text);
        $nbCharToParse = strlen($toParse);
        $nbCharId = 0;
        $isFormated = $toParse[0] === '(' ? true : false;
        $maxCharId = $isFormated ? self::MAX_ID_FORMATED : self::MAX_ID_NOT_FORMATED;
        $id = strtolower(substr($toParse, 0, min($maxCharId, $nbCharToParse)));
        $id = $isFormated ? $this->findIdFormated($id, $yAlreadySet, $realNameId) : $this->findIdNotFormated($id, $yAlreadySet, $realNameId);

        if ($id === false) {
            if ($this->allowsUnknownIdentifier === false) {
                return false;
            }

            $id = null;
            $nbCharId = 0;
            $content = $toParse;
        } else {
            $nbCharId = strlen($id) + ($isFormated ? 2 : 0);
            $n = min($this->identifiersAi[$realNameId][self::MAXLENGTH], $nbCharToParse);
            $content = substr($toParse, $nbCharId, $n);
        }

        if ($id !== null) {
            /* If we have an AI with an "y" var, we check if there is a decimal point in the next *MAXLENGTH* characters */
            /* if there is one, we take an extra character */
            if ($yAlreadySet !== null) {
                if (strpos($content, '.') !== false || strpos($content, ',') !== false) {
                    $n++;
                    if ($n <= $nbCharToParse) {
                        /* We take an extra char */
                        $content = substr($toParse, $nbCharId, $n);
                    }
                }
            }
        }

        /* We check for separator */
        $separator = strpos($content, chr(29));
        if ($separator !== false) {
            $content = substr($content, 0, $separator);
            $separatorsFound++;
        }

        if ($id !== null) {
            /* We check the conformity */
            if (!$this->checkConformity($content, $id, $realNameId)) {
                return false;
            }

            /* We check the checksum */
            if (!$this->checkChecksum($content, $id, $realNameId, $checksumAdded)) {
                return false;
            }

            /* We check the vars */
            if (!$this->checkVars($content, $id, $yAlreadySet, $decimalPointRemoved)) {
                return false;
            }
        }

        $this->identifiersId[] = $id;
        $this->identifiersContent[] = $content;

        $nbCharLastContent = (((strlen($content) + $nbCharId) - $checksumAdded) + $decimalPointRemoved) + $separatorsFound;
        if ($nbCharToParse - $nbCharLastContent > 0) {
            /* If there is more than one content in this array, we parse again */
            $otherContent = substr($toParse, $nbCharLastContent, $nbCharToParse);
            $nbCharOtherContent = strlen($otherContent);

            if ($otherContent[0] === chr(29)) {
                $otherContent = substr($otherContent, 1);
                $nbCharOtherContent--;
            }

            if ($nbCharOtherContent > 0) {
                $text = $otherContent;
                return $this->parseContent($text);
            }
        }

        return true;
    }

    /**
     * Checks if an id exists.
     *
     * @param string $id
     * @param bool $yAlreadySet
     * @param string $realNameId
     * @return bool
     */
    private function idExists($id, &$yAlreadySet, &$realNameId) {
        $yFound = isset($id[3]) && $id[3] === 'y';
        $idVarAdded = substr($id, 0, -1) . 'y';

        if (isset($this->identifiersAi[$id])) {
            if ($yFound) {
                $yAlreadySet = false;
            }

            $realNameId = $id;
            return true;
        } elseif (!$yFound && isset($this->identifiersAi[$idVarAdded])) {
            /* if the id don't exist, we try to find this id with "y" at the last char */
            $yAlreadySet = true;
            $realNameId = $idVarAdded;
            return true;
        }

        return false;
    }

    /**
     * Finds ID with formated content.
     *
     * @param string $id
     * @param bool $yAlreadySet
     * @param string $realNameId
     * @return mixed
     */
    private function findIdFormated($id, &$yAlreadySet, &$realNameId) {
        $pos = strpos($id, ')');
        if ($pos === false) {
            throw new BCGParseException('gs1128', 'Identifiers must have no more than 4 characters.');
        } else {
            if ($pos < 3) {
                throw new BCGParseException('gs1128', 'Identifiers must have at least 2 characters.');
            }

            $id = substr($id, 1, $pos - 1);
            if ($this->idExists($id, $yAlreadySet, $realNameId)) {
                return $id;
            }

            if ($this->allowsUnknownIdentifier === false) {
                throw new BCGParseException('gs1128', 'The identifier ' . $id . ' doesn\'t exist.');
            }

            return false;
        }
    }

    /**
     * Finds ID with non-formated content.
     *
     * @param string $id
     * @param bool $yAlreadySet
     * @param string $realNameId
     * @return mixed
     */
    private function findIdNotFormated($id, &$yAlreadySet, &$realNameId) {
        $tofind = $id;

        while (strlen($tofind) >= 2) {
            if ($this->idExists($tofind, $yAlreadySet, $realNameId)) {
                return $tofind;
            } else {
                $tofind = substr($tofind, 0, -1);
            }
        }

        if ($this->allowsUnknownIdentifier === false) {
            throw new BCGParseException('gs1128', 'Error in formatting, can\'t find an identifier.');
        }

        return false;
    }

    /**
     * Checks confirmity of the content.
     *
     * @param string $content
     * @param string $id
     * @param string $realNameId
     * @return bool
     */
    private function checkConformity(&$content, $id, $realNameId) {
        switch ($this->identifiersAi[$realNameId][self::KIND_OF_DATA]) {
            case self::NUMERIC:
                $content = str_replace(',', '.', $content);
                if (!preg_match("/^[0-9.]+$/", $content)) {
                    throw new BCGParseException('gs1128', 'The value of "' . $id . '" must be numerical.');
                }

                break;
            case self::DATE_YYMMDD:
                $valid_date = true;
                if (preg_match("/^[0-9]{6}$/", $content)) {
                    $year = substr($content, 0, 2);
                    $month = substr($content, 2, 2);
                    $day = substr($content, 4, 2);

                    /* day can be 00 if we only need month and year */
                    if (intval($month) < 1 || intval($month) > 12 || intval($day) < 0 || intval($day) > 31) {
                        $valid_date = false;
                    }
                } else {
                    $valid_date = false;
                }

                if (!$valid_date) {
                    throw new BCGParseException('gs1128', 'The value of "' . $id . '" must be in YYMMDD format.');
                }

                break;
        }

        // We check the length of the content
        $nbCharContent = strlen($content);
        $checksumChar = 0;
        $minlengthContent = $this->identifiersAi[$realNameId][self::MINLENGTH];
        $maxlengthContent = $this->identifiersAi[$realNameId][self::MAXLENGTH];

        if ($this->identifiersAi[$realNameId][self::CHECKSUM]) {
            $checksumChar++;
        }

        if ($nbCharContent < ($minlengthContent - $checksumChar)) {
            if ($minlengthContent === $maxlengthContent) {
                throw new BCGParseException('gs1128', 'The value of "' . $id . '" must contain ' . $minlengthContent . ' character(s).');
            } else {
                throw new BCGParseException('gs1128', 'The value of "' . $id . '" must contain between ' . $minlengthContent . ' and ' . $maxlengthContent . ' character(s).');
            }
        }

        return true;
    }

    /**
     * Verifies the checksum.
     *
     * @param string $content
     * @param string $id
     * @param int $realNameId
     * @param int $checksumAdded
     * @return bool
     */
    private function checkChecksum(&$content, $id, $realNameId, &$checksumAdded) {
        if ($this->identifiersAi[$realNameId][self::CHECKSUM]) {
            $nbCharContent = strlen($content);
            $minlengthContent = $this->identifiersAi[$realNameId][self::MINLENGTH];
            if ($nbCharContent === ($minlengthContent - 1)) {
                /* we need to calculate the checksum */
                $content .= self::getAiContentChecksum($content);
                $checksumAdded++;
            } elseif ($nbCharContent === $minlengthContent) {
                /* we need to check the checksum */
                $checksum = self::getAiContentChecksum(substr($content, 0, -1));
                if (intval($content[$nbCharContent - 1]) !== $checksum) {
                    throw new BCGParseException('gs1128', 'The checksum of "(' . $id . ') ' . $content . '" must be: ' . $checksum);
                }
            }
        }

        return true;
    }

    /**
     * Checks vars "y".
     *
     * @param string $content
     * @param string $id
     * @param bool $yAlreadySet
     * @param int $decimalPointRemoved
     * @return bool
     */
    private function checkVars(&$content, &$id, $yAlreadySet, &$decimalPointRemoved) {
        $nbCharContent = strlen($content);
        /* We check for "y" var in AI */
        if ($yAlreadySet) {
            /* We'll check if we have a decimal point */
            if (strpos($content, '.') !== false) {
                throw new BCGParseException('gs1128', 'If you do not use any "y" variable, you have to insert a whole number.');
            }
        } elseif ($yAlreadySet !== null) {
            /* We need to replace the "y" var with the position of the decimal point */
            $pos = strpos($content, '.');
            if ($pos === false) {
                $pos = $nbCharContent - 1;
            }

            $id = str_replace('y', $nbCharContent - ($pos + 1), strtolower($id));
            $content = str_replace('.', '', $content);
            $decimalPointRemoved++;
        }

        return true;
    }

    /**
     * Checksum Mod10.
     *
     * @param int $content
     * @return int
     */
    private static function calculateChecksumMod10($content) {
        // Calculating Checksum
        // Consider the right-most digit of the message to be in an "odd" position,
        // and assign odd/even to each character moving from right to left
        // Odd Position = 3, Even Position = 1
        // Multiply it by the number
        // Add all of that and do 10-(?mod10)
        $odd = true;
        $checksumValue = 0;
        $c = strlen($content);

        for ($i = $c; $i > 0; $i--) {
            if ($odd === true) {
                $multiplier = 3;
                $odd = false;
            } else {
                $multiplier = 1;
                $odd = true;
            }

            $checksumValue += ($content[$i - 1] * $multiplier);
        }

        return (10 - $checksumValue % 10) % 10;
    }
}
?>