<?php

namespace Racecore\GATracking\Tracking;

use Racecore\GATracking\Exception;

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
abstract class AbstractTracking
{
    // document referrer
    /** @var String */
    private $documentReferrer;

    // campaign
    /** @var String */
    private $campaignName;
    private $campaignSource;
    private $campaignMedium;
    private $campaignContent;
    private $campaignID;
    private $campaignKeyword;

    // adwords id
    /** @var String */
    private $adwordsID;

    // display ads id
    /** @var String */
    private $displayAdsID;

    // screen resolution
    /** @var String */
    private $screenResolution;

    // viewport size
    /** @var String */
    private $viewportSize;

    // document encoding
    /** @var String */
    private $documentEncoding;

    // screen colors
    /** @var String */
    private $screenColors;

    // user language
    /** @var String */
    private $userLanguage;

    // java enabled
    /** @var boolean|string */
    private $javaEnabled = null;

    // flash version
    /** @var String */
    private $flashVersion;

    // document location
    /** @var String */
    private $documentLocation;

    // document host
    /** @var String */
    private $documentHost;

    // document path
    /** @var String */
    private $documentPath;

    // document title
    /** @var String */
    private $documentTitle;

    // app name
    /** @var String */
    private $appName;

    // app version
    /** @var String */
    private $appVersion;

    // experiment id
    /** @var String */
    private $experimentID;

    // experiment variant
    /** @var String */
    private $experimentVariant;

    // content description
    /** @var String */
    private $contentDescription;

    // link id
    /** @var String */
    private $linkID;

    // custom dimensions
    /** @var Array */
    private $customDimension = array();

    // custom metric
    /** @var Array */
    private $customMetric = array();

    // productId
    /** @var string */
    private $productId;

    // non interactive hit
    private $nonInteractionHit = false;

    private $customPayload = array();

    // event queue time difference
    private $queueTime;

    /**
     * Add Custom Tracking Payload Data send to Google
     * @param $key
     * @param $value
     * @throws Exception\InvalidArgumentException
     */
    public function addCustomPayloadData($key, $value)
    {
        if (!is_string($value)) {
            throw new Exception\InvalidArgumentException('Custom payload data value must be a string');
        }

        $this->customPayload[$key] = $value;
    }

    /**
     * Get the transfer Paket from current Event
     *
     * @return array
     */
    abstract public function createPackage();

    /**
     * @return array
     */
    public function getPackage()
    {
        $package = array_merge($this->createPackage(), array(
            // campaign
            'cn' => $this->campaignName,
            'cs' => $this->campaignSource,
            'cm' => $this->campaignMedium,
            'ck' => $this->campaignKeyword,
            'cc' => $this->campaignContent,
            'ci' => $this->campaignID,

            // other
            'dr' => $this->documentReferrer,
            'gclid' => $this->adwordsID,
            'dclid' => $this->displayAdsID,

            // system info
            'sr' => $this->screenResolution,
            'sd' => $this->screenColors,
            'vp' => $this->viewportSize,
            'de' => $this->documentEncoding,
            'ul' => $this->userLanguage,
            'je' => $this->javaEnabled,
            'fl' => $this->flashVersion,

            // Content Information
            'dl' => $this->documentLocation,
            'dh' => $this->documentHost,
            'dp' => $this->documentPath,
            'dt' => $this->documentTitle,
            'cd' => $this->contentDescription,
            'linkid' => $this->linkID,

            // app tracking
            'an' => $this->appName,
            'av' => $this->appVersion,

            // non interactive hit
            'ni' => $this->nonInteractionHit,

            // content experiments
            'xid' => $this->experimentID,
            'xvar' => $this->experimentVariant,

            // optional
            'qt' => $this->queueTime,
        ));

        $package = $this->addCustomParameters($package);

        // custom payload data
        $package = array_merge($package, $this->customPayload);

        // remove all unused
        $package = array_filter($package, 'strlen');

        return $package;
    }

    /**
     * Set the Tracking Processing Time to pass the qt param within this tracking request
     * ATTENTION!: Values greater than four hours may lead to hits not being processed.
     *
     * https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#qt
     *
     * @param $milliseconds
     */
    public function setQueueTime($milliseconds)
    {
        $this->queueTime = $milliseconds;
    }

    /**
     * Mark the Hit as Non Interactive
     *
     * @param $bool
     */
    public function setAsNonInteractionHit($bool)
    {
        $this->nonInteractionHit = (bool) $bool;
    }

    /**
     * @param array $package
     * @return array
     */
    private function addCustomParameters($package)
    {
        // add custom metric params
        foreach ($this->customMetric as $id => $value) {
            $package['cm' . (int) $id ] = $value;
        }

        // add custom dimension params
        foreach ($this->customDimension as $id => $value) {
            $package['cd' . (int) $id ] = $value;
        }

        return $package;
    }

    /**
     * @param null $identifier
     * @param $value
     */
    public function setCustomDimension($value, $identifier = null)
    {
        $this->customDimension[$identifier] = $value;
    }

    /**
     * @param null $identifier
     * @param $value
     */
    public function setCustomMetric($value, $identifier = null)
    {
        $this->customMetric[$identifier] = $value;
    }

    /**
     * @param String $contentDescription
     */
    public function setContentDescription($contentDescription)
    {
        $this->contentDescription = $contentDescription;
    }

    /**
     * @param String $linkID
     */
    public function setLinkID($linkID)
    {
        $this->linkID = $linkID;
    }

    /**
     * @param String $adwordsID
     */
    public function setAdwordsID($adwordsID)
    {
        $this->adwordsID = $adwordsID;
    }

    /**
     * @param String $appName
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * @param String $appVersion
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;
    }

    /**
     * @param String $campaignContent
     */
    public function setCampaignContent($campaignContent)
    {
        $this->campaignContent = $campaignContent;
    }

    /**
     * @param String $campaignID
     */
    public function setCampaignID($campaignID)
    {
        $this->campaignID = $campaignID;
    }

    /**
     * @param String $campaignKeyword
     */
    public function setCampaignKeyword($campaignKeyword)
    {
        $this->campaignKeyword = $campaignKeyword;
    }

    /**
     * @deprecated Use setCampaignKeyword
     * @param $campaignKeyword
     */
    public function setCampaignKeywords($campaignKeyword)
    {
        if (is_array($campaignKeyword)) {
            $campaignKeyword = implode(',', $campaignKeyword);
        }

        $this->setCampaignKeyword($campaignKeyword);
    }

    /**
     * @param String $campaignMedium
     */
    public function setCampaignMedium($campaignMedium)
    {
        $this->campaignMedium = $campaignMedium;
    }

    /**
     * @param String $campaignName
     */
    public function setCampaignName($campaignName)
    {
        $this->campaignName = $campaignName;
    }

    /**
     * @param String $campaignSource
     */
    public function setCampaignSource($campaignSource)
    {
        $this->campaignSource = $campaignSource;
    }

    /**
     * @param String $displayAdsID
     */
    public function setDisplayAdsID($displayAdsID)
    {
        $this->displayAdsID = $displayAdsID;
    }

    /**
     * @param String $documentEncoding
     */
    public function setDocumentEncoding($documentEncoding)
    {
        $this->documentEncoding = $documentEncoding;
    }

    /**
     * @param String $documentHost
     */
    public function setDocumentHost($documentHost)
    {
        $this->documentHost = $documentHost;
    }

    /**
     * @param String $documentLocation
     */
    public function setDocumentLocation($documentLocation)
    {
        $this->documentLocation = $documentLocation;
    }

    /**
     * @param String $documentPath
     */
    public function setDocumentPath($documentPath)
    {
        $this->documentPath = $documentPath;
    }

    /**
     * @param String $documentReferrer
     */
    public function setDocumentReferrer($documentReferrer)
    {
        $this->documentReferrer = $documentReferrer;
    }

    /**
     * @param String $documentTitle
     */
    public function setDocumentTitle($documentTitle)
    {
        $this->documentTitle = $documentTitle;
    }

    /**
     * @param String $experimentID
     */
    public function setExperimentID($experimentID)
    {
        $this->experimentID = $experimentID;
    }

    /**
     * @param String $experimentVariant
     */
    public function setExperimentVariant($experimentVariant)
    {
        $this->experimentVariant = $experimentVariant;
    }

    /**
     * @param String $flashVersion
     */
    public function setFlashVersion($flashVersion)
    {
        $this->flashVersion = $flashVersion;
    }

    /**
     * @param boolean $javaEnabled
     */
    public function setJavaEnabled($javaEnabled)
    {
        $this->javaEnabled = (bool) $javaEnabled;
    }

    /**
     * @param String $screenColors
     */
    public function setScreenColors($screenColors)
    {
        $this->screenColors = $screenColors;
    }

    /**
     * @param $width
     * @param $height
     */
    public function setScreenResolution($width, $height)
    {
        $this->screenResolution = $width . 'x' . $height;
    }

    /**
     * @param String $userLanguage
     */
    public function setUserLanguage($userLanguage)
    {
        $this->userLanguage = $userLanguage;
    }

    /**
     * @param $width
     * @param $height
     */
    public function setViewportSize($width, $height)
    {
        $this->viewportSize = $width . 'x' . $height;
    }

    /**
     * @param string $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }
}
