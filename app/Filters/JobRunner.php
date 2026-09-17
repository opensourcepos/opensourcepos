<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Tasks\TaskRunner;
use Config\OSPOS;
use Config\Tasks;
use Throwable;

/**
 * Drives the CI4 Tasks scheduler on web requests when Jobs mode is set to
 * 'web', so due tasks (e.g. jobs_heartbeat) run without relying on an
 * external cron / Task Scheduler process hitting `php spark tasks:run`.
 *
 * Task execution is deferred to a shutdown function so it runs after the
 * response has been sent (CodeIgniter::sendResponse() happens after all
 * after-filters, so it can't be sent early from here). Under PHP-FPM,
 * fastcgi_finish_request() closes the client connection first so task
 * processing happens without the client waiting on it.
 */
class JobRunner implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $config = config(OSPOS::class)->settings;

        if (($config['jobs_mode'] ?? 'web') !== 'web') {
            return null;
        }

        $throttleKey = 'job_runner_web_' . date('YmdHi');
        $cache = cache();

        if ($cache->get($throttleKey) !== null) {
            return null;
        }

        $cache->save($throttleKey, true, 65);

        $maxSeconds = (int)($config['jobs_web_max_seconds'] ?? config('Jobs')->webMaxSeconds);

        register_shutdown_function(static function () use ($maxSeconds): void {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            $start = microtime(true);

            try {
                config(Tasks::class)->init(service('scheduler'));

                $runner = new TaskRunner();

                if (microtime(true) - $start < $maxSeconds) {
                    $runner->run();
                }
            } catch (Throwable $e) {
                log_message('error', 'JobRunner filter failed: ' . $e->getMessage());
            }
        });

        return null;
    }
}
