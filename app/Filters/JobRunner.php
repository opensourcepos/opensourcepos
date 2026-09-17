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
        $lockHandle = $this->acquireLock($throttleKey);

        if ($lockHandle === null) {
            return null;
        }

        $maxSeconds = (int)($config['jobs_web_max_seconds'] ?? config('Jobs')->webMaxSeconds);

        register_shutdown_function(static function () use ($maxSeconds, $lockHandle): void {
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
            } finally {
                flock($lockHandle, LOCK_UN);
                fclose($lockHandle);
            }
        });

        return null;
    }

    /**
     * Atomically claims the per-minute run slot via an exclusive, non-blocking
     * flock() on a lock file named after the throttle key. Only the request
     * that wins the lock gets a handle back; concurrent requests get null and
     * must not schedule work. The handle is kept open (and unlocked/closed in
     * the shutdown function) so the lock is held for the life of the request,
     * not just this check.
     */
    private function acquireLock(string $throttleKey)
    {
        $lockFile = WRITEPATH . 'jobs/' . preg_replace('/[^A-Za-z0-9_-]/', '_', $throttleKey) . '.lock';

        if (!is_dir(dirname($lockFile))) {
            mkdir(dirname($lockFile), 0755, true);
        }

        $handle = fopen($lockFile, 'c');

        if ($handle === false) {
            return null;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return null;
        }

        $this->pruneStaleLocks(dirname($lockFile));

        return $handle;
    }

    /**
     * Removes lock files older than 1 hour so writable/jobs/ doesn't
     * accumulate one file per minute forever.
     */
    private function pruneStaleLocks(string $dir): void
    {
        foreach (glob($dir . '/*.lock') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < time() - 3600) {
                @unlink($file);
            }
        }
    }
}
