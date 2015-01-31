<?php
/**
 *--------------------------------------------------------------------
 *
 * Draw Exception
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
class BCGDrawException extends Exception {
    /**
     * Constructor with specific message.
     *
     * @param string $message
     */
    public function __construct($message) {
        parent::__construct($message, 30000);
    }
}
?>