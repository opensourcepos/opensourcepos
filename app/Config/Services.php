<?php

namespace Config;

use App\Jobs\JobThrottleGate;
use App\Libraries\ImportBatchService;
use App\Libraries\MY_Language;
use App\Models\JobThrottle;
use Locale;
use HTMLPurifier;
use HTMLPurifier_Config;
use CodeIgniter\Config\BaseService;
use Config\Services as AppServices;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    /**
     * Responsible for loading the language string translations.
     *
     * @param string|null $locale
     * @param bool $getShared
     * @return MY_Language
     */
    public static function language(?string $locale = null, bool $getShared = true): MY_Language
    {
        if ($getShared) {
            return static::getSharedInstance('language', $locale)->setLocale($locale);
        }

        if (AppServices::get('request') instanceof IncomingRequest) {
            $requestLocale = AppServices::get('request')->getLocale();
        } else {
            $requestLocale = Locale::getDefault();
        }

        // Use '?:' for empty string check
        $locale = $locale ?: $requestLocale;

        return new MY_Language($locale);
    }

    private static HTMLPurifier $htmlPurifier;

    public static function htmlPurifier($getShared = true): object
    {
        if ($getShared) {
            return static::getSharedInstance('htmlPurifier');
        }

        if (!isset(static::$htmlPurifier)) {
            $config = HTMLPurifier_Config::createDefault();
            static::$htmlPurifier = new HTMLPurifier($config);
        }

        return static::$htmlPurifier;
    }

    /**
     * Tracks progress of a queued CSV import (issue #3833 Phase 3).
     *
     * @param bool $getShared
     * @return ImportBatchService
     */
    public static function importBatch(bool $getShared = true): ImportBatchService
    {
        if ($getShared) {
            return static::getSharedInstance('importBatch');
        }

        return new ImportBatchService();
    }

    /**
     * Gates queue draining against the configured job throttles. Bound as a
     * service (rather than constructed directly by callers) so a future
     * plugin can override this binding to layer its own throttling on top
     * of, or instead of, the core job_throttles rows.
     *
     * @param bool $getShared
     * @return JobThrottleGate
     */
    public static function jobThrottleGate(bool $getShared = true): JobThrottleGate
    {
        if ($getShared) {
            return static::getSharedInstance('jobThrottleGate');
        }

        return new JobThrottleGate(model(JobThrottle::class));
    }
}
