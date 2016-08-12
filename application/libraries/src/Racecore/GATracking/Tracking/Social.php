<?php

namespace Racecore\GATracking\Tracking;

use Racecore\GATracking\Exception\MissingTrackingParameterException;

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
class Social extends AbstractTracking
{
    /** @var  String */
    private $socialAction;

    /** @var  String */
    private $socialNetwork;

    /** @var  String */
    private $socialTarget;

    /**
     * Set the Social Action (Required)
     *
     * @param $socialAction
     * @return $this
     */
    public function setSocialAction($socialAction)
    {
        $this->socialAction = $socialAction;
        return $this;
    }

    /**
     * Get the Social Action
     *
     * @return String
     */
    public function getSocialAction()
    {
        return $this->socialAction;
    }

    /**
     * Set the Social Network (Required)
     *
     * @param $socialNetwork
     * @return $this
     */
    public function setSocialNetwork($socialNetwork)
    {
        $this->socialNetwork = $socialNetwork;
        return $this;
    }

    /**
     * Get the Social Network
     *
     * @return String
     */
    public function getSocialNetwork()
    {
        return $this->socialNetwork;
    }

    /**
     * Set the Social Target (Required)
     *
     * @param $socialTarget
     * @return $this
     */
    public function setSocialTarget($socialTarget)
    {
        $this->socialTarget = $socialTarget;
        return $this;
    }

    /**
     * Get the Social Target
     *
     * @return String
     */
    public function getSocialTarget()
    {
        return $this->socialTarget;
    }

    /**
     * Returns the Google Paket for Social Tracking
     *
     * @return array
     * @throws \Racecore\GATracking\Exception\MissingTrackingParameterException
     */
    public function createPackage()
    {
        if (!$this->getSocialAction()) {
            throw new MissingTrackingParameterException('social action musst be set');
        }

        if (!$this->getSocialNetwork()) {
            throw new MissingTrackingParameterException('social network musst be set');
        }

        if (!$this->getSocialTarget()) {
            throw new MissingTrackingParameterException('social target musst be set');
        }

        return array(
            't' => 'social',
            'sa' => $this->getSocialAction(),
            'sn' => $this->getSocialNetwork(),
            'st' => $this->getSocialTarget()
        );
    }
}
