<?php
/**
 *--------------------------------------------------------------------
 *
 * Image Class to draw PNG images with possibility to set DPI
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

class BCGDrawPNG extends BCGDraw {
    private $dpi;
    
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
        if (is_numeric($dpi)) {
            $this->dpi = max(1, $dpi);
        } else {
            $this->dpi = null;
        }
    }

    /**
     * Draws the PNG on the screen or in a file.
     */
    public function draw() {
        ob_start();
        imagepng($this->im);
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
        // Scan all the ChunkType
        if (strcmp(substr($bin, 0, 8), pack('H*', '89504E470D0A1A0A')) === 0) {
            $chunks = $this->detectChunks($bin);

            $this->internalSetDPI($bin, $chunks);
            $this->internalSetC($bin, $chunks);
        }
    }

    private function detectChunks($bin) {
        $data = substr($bin, 8);
        $chunks = array();
        $c = strlen($data);
        
        $offset = 0;
        while ($offset < $c) {
            $packed = unpack('Nsize/a4chunk', $data);
            $size = $packed['size'];
            $chunk = $packed['chunk'];

            $chunks[] = array('offset' => $offset + 8, 'size' => $size, 'chunk' => $chunk);
            $jump = $size + 12;
            $offset += $jump;
            $data = substr($data, $jump);
        }
        
        return $chunks;
    }

    private function internalSetDPI(&$bin, &$chunks) {
        if ($this->dpi !== null) {
            $meters = (int)($this->dpi * 39.37007874);

            $found = -1;
            $c = count($chunks);
            for($i = 0; $i < $c; $i++) {
                // We already have a pHYs
                if($chunks[$i]['chunk'] === 'pHYs') {
                    $found = $i;
                    break;
                }
            }

            $data = 'pHYs' . pack('NNC', $meters, $meters, 0x01);
            $crc = self::crc($data, 13);
            $cr = pack('Na13N', 9, $data, $crc);

            // We didn't have a pHYs
            if($found == -1) {
                // Don't do anything if we have a bad PNG
                if($c >= 2 && $chunks[0]['chunk'] === 'IHDR') {
                    array_splice($chunks, 1, 0, array(array('offset' => 33, 'size' => 9, 'chunk' => 'pHYs')));

                    // Push the data
                    for($i = 2; $i < $c; $i++) {
                        $chunks[$i]['offset'] += 21;
                    }

                    $firstPart = substr($bin, 0, 33);
                    $secondPart = substr($bin, 33);
                    $bin = $firstPart;
                    $bin .= $cr;
                    $bin .= $secondPart;
                }
            } else {
                $bin = substr_replace($bin, $cr, $chunks[$i]['offset'], 21);
            }
        }
    }

    private function internalSetC(&$bin, &$chunks) {
        if (count($chunks) >= 2 && $chunks[0]['chunk'] === 'IHDR') {
            $firstPart = substr($bin, 0, 33);
            $secondPart = substr($bin, 33);
            $cr = pack('H*', '0000004C74455874436F707972696768740047656E657261746564207769746820426172636F64652047656E657261746F7220666F722050485020687474703A2F2F7777772E626172636F64657068702E636F6D597F70B8');
            $bin = $firstPart;
            $bin .= $cr;
            $bin .= $secondPart;
        }
        
        // Chunks is dirty!! But we are done.
    }

    private static $crc_table = array();
    private static $crc_table_computed = false;

    private static function make_crc_table() {
        for ($n = 0; $n < 256; $n++) {
            $c = $n;
            for ($k = 0; $k < 8; $k++) {
                if (($c & 1) == 1) {
                    $c = 0xedb88320 ^ (self::SHR($c, 1));
                } else {
                    $c = self::SHR($c, 1);
                }
            }
            self::$crc_table[$n] = $c;
        }

        self::$crc_table_computed = true;
    }

    private static function SHR($x, $n) {
        $mask = 0x40000000;

        if ($x < 0) {
            $x &= 0x7FFFFFFF;
            $mask = $mask >> ($n - 1);
            return ($x >> $n) | $mask;
        }

        return (int)$x >> (int)$n;
    }

    private static function update_crc($crc, $buf, $len) {
        $c = $crc;

        if (!self::$crc_table_computed) {
            self::make_crc_table();
        }

        for ($n = 0; $n < $len; $n++) {
            $c = self::$crc_table[($c ^ ord($buf[$n])) & 0xff] ^ (self::SHR($c, 8));
        }

        return $c;
    }

    private static function crc($data, $len) {
        return self::update_crc(-1, $data, $len) ^ -1;
    }
}
?>
