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
class Event extends AbstractTracking
{
    /** @var  String */
    private $eventCategory;

    /** @var  String */
    private $eventAction;

    /** @var  String */
    private $eventLabel;

    /** @var  String */
    private $eventValue;

    /**
     * Set the Event Action (Required)
     *
     * @param $eventAction
     * @return $this
     */
    public function setEventAction($eventAction)
    {
        $this->eventAction = $eventAction;
        return $this;
    }

    /**
     * Get the Event Action
     *
     * @return $this
     */
    public function getEventAction()
    {
        return $this->eventAction;
    }

    /**
     * Set the Event Category (Required)
     *
     * @param $eventCategory
     * @return $this
     */
    public function setEventCategory($eventCategory)
    {
        $this->eventCategory = $eventCategory;
        return $this;
    }

    /**
     * Get the Event Category
     *
     * @return $this
     */
    public function getEventCategory()
    {
        return $this->eventCategory;
    }

    /**
     * Set the Event Label
     *
     * @param $eventLabel
     * @return $this
     */
    public function setEventLabel($eventLabel)
    {
        $this->eventLabel = $eventLabel;
        return $this;
    }

    /**
     * Get the Event Label
     *
     * @return $this
     */
    public function getEventLabel()
    {
        return $this->eventLabel;
    }

    /**
     * Set the Event Value
     *
     * @param $eventValue
     * @return $this
     */
    public function setEventValue($eventValue)
    {
        $this->eventValue = $eventValue;
        return $this;
    }

    /**
     * Get the Event Value
     *
     * @return $this
     */
    public function getEventValue()
    {
        return $this->eventValue;
    }

    /**
     * Returns the Paket for Event Tracking
     *
     * @return array
     * @throws \Racecore\GATracking\Exception\MissingTrackingParameterException
     */
    public function createPackage()
    {
        if (!$this->getEventCategory()) {
            throw new MissingTrackingParameterException('event category musst be set');
        }

        if (!$this->getEventAction()) {
            throw new MissingTrackingParameterException('event action musst be set');
        }

        return array(
            't' => 'event',
            'ec' => $this->getEventCategory(),
            'ea' => $this->getEventAction(),
            'el' => $this->getEventLabel(),
            'ev' => $this->getEventValue()
        );
    }
}
