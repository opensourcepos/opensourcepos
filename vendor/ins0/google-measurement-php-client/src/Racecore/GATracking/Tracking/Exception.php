<?php

namespace Racecore\GATracking\Tracking;

/**
 * Google Analytics Measurement PHP Class
 * Licensed under the 3-clause BSD License.
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * Google Documentation
 * https://developers.google.com/analytics/devguides/collection/protocol/v1/
 *
 * @author  Marco Rieger
 * @email   Rieger(at)racecore.de
 * @git     https://github.com/ins0
 * @url     http://www.racecore.de
 * @package Racecore\GATracking\Tracking
 */
class Exception extends AbstractTracking
{
    /** @var string */
    private $exceptionDescription;

    /** @var bool */
    private $exceptionFatal;

    /**
     * Set the Exception Description
     *
     * @param $exceptionDescription
     * @return $this
     */
    public function setExceptionDescription($exceptionDescription)
    {
        $this->exceptionDescription = $exceptionDescription;
        return $this;
    }

    /**
     * Get the Exception Description
     *
     * @return String
     */
    public function getExceptionDescription()
    {
        return $this->exceptionDescription;
    }

    /**
     * Set if Exception is fatal
     *
     * @param $exceptionFatal
     * @return $this
     */
    public function setExceptionFatal($exceptionFatal)
    {
        $this->exceptionFatal = (bool) $exceptionFatal;
        return $this;
    }

    /**
     * Get Exception is fatal
     *
     * @return bool
     */
    public function getExceptionFatal()
    {
        return $this->exceptionFatal;
    }

    /**
     * Returns the Paket for Exception Tracking
     *
     * @return array
     */
    public function createPackage()
    {
        return array(
            't' => 'exception',
            'exd' => $this->getExceptionDescription(),
            'exf' => ( $this->getExceptionFatal() ? '1' : '0' )
        );
    }
}
