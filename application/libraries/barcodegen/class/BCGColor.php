<?php
/**
 *--------------------------------------------------------------------
 *
 * Holds Color in RGB Format.
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
class BCGColor {
    protected $r, $g, $b;    // int Hexadecimal Value
    protected $transparent;

    /**
     * Save RGB value into the classes.
     *
     * There are 4 way to associate color with this classes :
     *  1. Gives 3 parameters int (R, G, B)
     *  2. Gives 1 parameter string hex value (#ff0000) (preceding with #)
     *  3. Gives 1 parameter int hex value (0xff0000)
     *  4. Gives 1 parameter string color code (white, black, orange...)
     *
     * @param mixed ...
     */
    public function __construct() {
        $args = func_get_args();
        $c = count($args);
        if ($c === 3) {
            $this->r = intval($args[0]);
            $this->g = intval($args[1]);
            $this->b = intval($args[2]);
        } elseif ($c === 1) {
            if (is_string($args[0]) && strlen($args[0]) === 7 && $args[0][0] === '#') {        // Hex Value in String
                $this->r = intval(substr($args[0], 1, 2), 16);
                $this->g = intval(substr($args[0], 3, 2), 16);
                $this->b = intval(substr($args[0], 5, 2), 16);
            } else {
                if (is_string($args[0])) {
                    $args[0] = self::getColor($args[0]);
                }

                $args[0] = intval($args[0]);
                $this->r = ($args[0] & 0xff0000) >> 16;
                $this->g = ($args[0] & 0x00ff00) >> 8;
                $this->b = ($args[0] & 0x0000ff);
            }
        } else {
            $this->r = $this->g = $this->b = 0;
        }
    }

    /**
     * Sets the color transparent.
     *
     * @param bool $transparent
     */
    public function setTransparent($transparent) {
        $this->transparent = $transparent;
    }

    /**
     * Returns Red Color.
     *
     * @return int
     */
    public function r() {
        return $this->r;
    }

    /**
     * Returns Green Color.
     *
     * @return int
     */
    public function g() {
        return $this->g;
    }

    /**
     * Returns Blue Color.
     *
     * @return int
     */
    public function b() {
        return $this->b;
    }

    /**
     * Returns the int value for PHP color.
     *
     * @param resource $im
     * @return int
     */
    public function allocate(&$im) {
        $allocated = imagecolorallocate($im, $this->r, $this->g, $this->b);
        if ($this->transparent) {
            return imagecolortransparent($im, $allocated);
        } else {
            return $allocated;
        }
    }

    /**
     * Returns class of BCGColor depending of the string color.
     *
     * If the color doens't exist, it takes the default one.
     *
     * @param string $code
     * @param string $default
     */
    public static function getColor($code, $default = 'white') {
        switch(strtolower($code)) {
            case '':
            case 'white':
                return 0xffffff;
            case 'black':
                return 0x000000;
            case 'maroon':
                return 0x800000;
            case 'red':
                return 0xff0000;
            case 'orange':
                return 0xffa500;
            case 'yellow':
                return 0xffff00;
            case 'olive':
                return 0x808000;
            case 'purple':
                return 0x800080;
            case 'fuchsia':
                return 0xff00ff;
            case 'lime':
                return 0x00ff00;
            case 'green':
                return 0x008000;
            case 'navy':
                return 0x000080;
            case 'blue':
                return 0x0000ff;
            case 'aqua':
                return 0x00ffff;
            case 'teal':
                return 0x008080;
            case 'silver':
                return 0xc0c0c0;
            case 'gray':
                return 0x808080;
            default:
                return self::getColor($default, 'white');
        }
    }
}
?>