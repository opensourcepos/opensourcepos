<?php

namespace Racecore\GATracking\Tracking\User;

use Racecore\GATracking\Tracking\AbstractTracking;

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
 * @package Racecore\GATracking\Tracking\User
 */
class Timing extends AbstractTracking
{
    /** @var string */
    private $timingCategory;

    /** @var string */
    private $timingVariable;

    /** @var integer */
    private $timingTime;

    /** @var string */
    private $timingLabel;

    /** @var integer */
    private $browserDnsLoadTime;

    /** @var integer */
    private $browserPageDownloadTime;

    /** @var integer */
    private $browserRedirectTime;

    /** @var integer */
    private $browserTcpConnectTime;

    /** @var integer */
    private $browserServerResponseTime;

    /**
     * Set the browser dns load time
     *
     * @param int $browserDnsLoadTime
     */
    public function setBrowserDnsLoadTime($browserDnsLoadTime)
    {
        $this->browserDnsLoadTime = $browserDnsLoadTime;
    }

    /**
     * Get the browser dns load time
     *
     * @return int
     */
    public function getBrowserDnsLoadTime()
    {
        return $this->browserDnsLoadTime;
    }

    /**
     * Set the browser page download time
     *
     * @param int $browserPageDownloadTime
     */
    public function setBrowserPageDownloadTime($browserPageDownloadTime)
    {
        $this->browserPageDownloadTime = $browserPageDownloadTime;
    }

    /**
     * Get the browser page download time
     * @return int
     */
    public function getBrowserPageDownloadTime()
    {
        return $this->browserPageDownloadTime;
    }

    /**
     * Set the browser redirect time
     *
     * @param int $browserRedirectTime
     */
    public function setBrowserRedirectTime($browserRedirectTime)
    {
        $this->browserRedirectTime = $browserRedirectTime;
    }

    /**
     * Get the browser redirect time
     *
     * @return int
     */
    public function getBrowserRedirectTime()
    {
        return $this->browserRedirectTime;
    }

    /**
     * Set the browser server response time
     *
     * @param int $browserServerResponseTime
     */
    public function setBrowserServerResponseTime($browserServerResponseTime)
    {
        $this->browserServerResponseTime = $browserServerResponseTime;
    }

    /**
     * Get the browser server response time
     *
     * @return int
     */
    public function getBrowserServerResponseTime()
    {
        return $this->browserServerResponseTime;
    }

    /**
     * Set the browser tcp conenct time
     *
     * @param int $browserTcpConnectTime
     */
    public function setBrowserTcpConnectTime($browserTcpConnectTime)
    {
        $this->browserTcpConnectTime = $browserTcpConnectTime;
    }

    /**
     * Get the browser tcp conenct time
     *
     * @return int
     */
    public function getBrowserTcpConnectTime()
    {
        return $this->browserTcpConnectTime;
    }

    /**
     * Set the timing category
     *
     * @param string $timingCategory
     */
    public function setTimingCategory($timingCategory)
    {
        $this->timingCategory = $timingCategory;
    }

    /**
     * Get the timing category
     *
     * @return string
     */
    public function getTimingCategory()
    {
        return $this->timingCategory;
    }

    /**
     * Set the timing label
     *
     * @param string $timingLabel
     */
    public function setTimingLabel($timingLabel)
    {
        $this->timingLabel = $timingLabel;
    }

    /**
     * Get the timing label
     *
     * @return string
     */
    public function getTimingLabel()
    {
        return $this->timingLabel;
    }

    /**
     * Set the timing time
     *
     * @param int $timingTime
     */
    public function setTimingTime($timingTime)
    {
        $this->timingTime = $timingTime;
    }

    /**
     * Get the timing time
     *
     * @return int
     */
    public function getTimingTime()
    {
        return $this->timingTime;
    }

    /**
     * Set the timing variable
     *
     * @param string $timingVariable
     */
    public function setTimingVariable($timingVariable)
    {
        $this->timingVariable = $timingVariable;
    }

    /**
     * Get the timing time
     *
     * @return string
     */
    public function getTimingVariable()
    {
        return $this->timingVariable;
    }

    /**
     * Returns the Paket for User Timing Tracking
     *
     * @return array
     */
    public function createPackage()
    {
        return array(
            't' => 'timing',
            'utc' => $this->getTimingCategory(),
            'utv' => $this->getTimingVariable(),
            'utt' => $this->getTimingTime(),
            'utl' => $this->getTimingLabel(),
            'dns' => $this->getBrowserDnsLoadTime(),
            'pdt' => $this->getBrowserPageDownloadTime(),
            'rrt' => $this->getBrowserRedirectTime(),
            'tcp' => $this->getBrowserTcpConnectTime(),
            'srt' => $this->getBrowserServerResponseTime()
        );
    }
}
