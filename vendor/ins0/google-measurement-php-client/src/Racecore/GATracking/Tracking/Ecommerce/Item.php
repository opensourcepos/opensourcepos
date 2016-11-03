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
class Item extends AbstractTracking
{

    private $tid = 0;
    private $name = '';
    private $price = 0;
    private $quantity = 0;
    private $sku = '';
    private $category = '';
    private $currency = '';

    /**
     * Set the Transaction ID
     *
     * @param $tid
     */
    public function setTransactionID($tid)
    {

        $this->tid = $tid;
    }

    /**
     * Returns the Transaction ID
     *
     * @return integer
     */
    public function getTransactionID()
    {

        if (!$this->tid) {
            return '/';
        }

        return $this->tid;
    }

    /**
     * Sets the Item Name
     *
     * @param $name
     */
    public function setName($name)
    {

        $this->name = $name;
    }

    /**
     * Return Name
     *
     * @return string
     */
    public function getName()
    {

        if (!$this->name) {
            return $this->sku;
        }

        return $this->name;
    }

    /**
     * Sets the Item Price
     *
     * @param $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Return the Price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Sets the Quantity
     *
     * @param $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Return Quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Sets the Sku
     *
     * @param $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * Return the Sku
     *
     * @return float
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Sets the Category
     *
     * @param $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Return the Category
     *
     * @return float
     */
    public function getCategory()
    {
        return $this->category;
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
     * Create the Package
     *
     * @return array
     * @throws \Racecore\GATracking\Exception\MissingTrackingParameterException
     */
    public function createPackage()
    {
        if (!$this->getTransactionID()) {
            throw new MissingTrackingParameterException('transaction id is missing');
        }

        if (!$this->getName()) {
            throw new MissingTrackingParameterException('item name is missing');
        }

        return array(
            't' => 'item',
            'ti' => $this->getTransactionID(),
            'in' => $this->getName(),
            'ip' => $this->getPrice(),
            'iq' => $this->getQuantity(),
            'ic' => $this->getSku(),
            'iv' => $this->getCategory(),
            'cu' => $this->getCurrency()
        );
    }
}
