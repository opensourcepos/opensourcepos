<?php

namespace Racecore\GATracking\Tracking\Ecommerce;

use Racecore\GATracking\Exception\MissingTrackingParameterException;
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
 * @author  Enea Berti
 * @email   reysharks(at)gmail.com
 * @git     https://github.com/reysharks
 * @url     http://www.adacto.it
 * @package Racecore\GATracking\Tracking
 */
class Transaction extends AbstractTracking
{

    private $id = 0;
    private $affiliation = '';
    private $revenue = 0;
    private $shipping = 0;
    private $tax = 0;
    private $currency = '';

    /**
     * @param $host
     * @deprecated
     */
    public function setTransactionHost($host)
    {
        return $this->setDocumentHost($host);
    }

    /**
     * Set the Transaction ID
     *
     * @param $id
     */
    public function setID($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the Transaction ID
     *
     * @return integer
     */
    public function getID()
    {

        if (!$this->id) {
            return '/';
        }

        return $this->id;
    }

    /**
     * Sets the Affiliation
     *
     * @param $affiliation
     */
    public function setAffiliation($affiliation)
    {

        $this->affiliation = $affiliation;
    }

    /**
     * Return Affiliation
     *
     * @return string
     */
    public function getAffiliation()
    {

        return $this->affiliation;
    }

    /**
     * Sets the Revenue
     *
     * @param $revenue
     */
    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;
    }

    /**
     * Return the Revenue
     *
     * @return float
     */
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     * Sets the Shipping
     *
     * @param $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * Return Shipping
     *
     * @return float
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Sets the Tax
     *
     * @param $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * Return the Tax
     *
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Sets the Currency
     *
     * @param $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Return the Currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Returns the Google Paket for Transaction Tracking
     *
     * @return array
     * @throws \Racecore\GATracking\Exception\MissingTrackingParameterException
     */
    public function createPackage()
    {
        if (!$this->getID()) {
            throw new MissingTrackingParameterException('transaction id is missing');
        }

        return array(
            't' => 'transaction',
            'ti' => $this->getID(),
            'ta' => $this->getAffiliation(),
            'tr' => $this->getRevenue(),
            'ts' => $this->getShipping(),
            'tt' => $this->getTax(),
            'cu' => $this->getCurrency()
        );
    }
}
