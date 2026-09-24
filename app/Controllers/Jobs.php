<?php

namespace App\Controllers;

use App\Jobs\BoundedQueueWorker;
use App\Models\Appconfig;
use App\Models\JobThrottle;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
use Config\Jobs as JobsConfig;
use ReflectionException;

class Jobs extends Secure_Controller
{
    private BaseConnection $db;
    private Appconfig $appconfig;
    private JobThrottle $jobThrottle;
    private array $config;

    public function __construct()
    {
        parent::__construct('jobs');

        $this->db = Database::connect();
        $this->appconfig = model(Appconfig::class);
        $this->jobThrottle = model(JobThrottle::class);
        $this->config = $this->global_view_data['config'];
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function getIndex(): string
    {
        $data['config'] = $this->config;
        $data['throttles'] = $this->jobThrottle->getAll()->getResultArray();
        $data['queues'] = config(JobsConfig::class)->coreQueues;

        return view('jobs/manage', $data);
    }

    /**
     * Saves settings configuration. Used in app/Views/jobs/settings_config.php
     *
     * @throws ReflectionException
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postSaveSettings(): ResponseInterface
    {
        $rules = [
            'mode'                  => 'required|in_list[auto,web,manual]',
            'web_max_seconds'       => 'required|is_natural',
            'task_max_seconds'      => 'required|is_natural',
            'retry_limit'           => 'required|is_natural_no_zero',
            'auto_purge'            => 'permit_empty|in_list[0,1]',
            'retention_days'        => 'required|is_natural',
            'failed_retention_days' => 'required|is_natural',
        ];

        $messages = [
            'mode'                  => ['in_list' => lang('Jobs.mode_invalid')],
            'web_max_seconds'       => ['is_natural' => lang('Jobs.web_max_seconds_invalid')],
            'task_max_seconds'      => ['is_natural' => lang('Jobs.task_max_seconds_invalid')],
            'retry_limit'           => ['is_natural_no_zero' => lang('Jobs.retry_limit_invalid')],
            'retention_days'        => ['is_natural' => lang('Jobs.retention_days_invalid')],
            'failed_retention_days' => ['is_natural' => lang('Jobs.failed_retention_days_invalid')],
        ];

        if ($response = $this->validateFields($rules, $messages)) {
            return $response;
        }

        $batchSaveData = [
            'jobs_mode'                  => $this->request->getPost('mode'),
            'jobs_web_max_seconds'       => $this->request->getPost('web_max_seconds', FILTER_SANITIZE_NUMBER_INT),
            'jobs_task_max_seconds'      => $this->request->getPost('task_max_seconds', FILTER_SANITIZE_NUMBER_INT),
            'jobs_retry_limit'           => $this->request->getPost('retry_limit', FILTER_SANITIZE_NUMBER_INT),
            'jobs_auto_purge'            => $this->request->getPost('auto_purge') ? '1' : '0',
            'jobs_retention_days'        => $this->request->getPost('retention_days', FILTER_SANITIZE_NUMBER_INT),
            'jobs_failed_retention_days' => $this->request->getPost('failed_retention_days', FILTER_SANITIZE_NUMBER_INT),
        ];

        $success = $this->appconfig->batch_save($batchSaveData);

        return $this->response->setJSON(['success' => $success, 'message' => lang('Jobs.saved_' . ($success ? '' : 'un') . 'successfully')]);
    }

    /**
     * Saves throttle configuration. Used in app/Views/jobs/settings_config.php
     *
     * @throws ReflectionException
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postSaveThrottles(): ResponseInterface
    {
        $allowedPeriods = ['second', 'minute', 'hour', 'day', 'month'];

        $this->db->transStart();

        $notToDelete = [];
        $arraySave = [];

        foreach ($this->request->getPost() as $key => $value) {
            if (str_starts_with($key, 'throttle_count_') && preg_match('/^throttle_count_(\d+)$/', $key, $matches)) {
                $throttleId = $matches[1];
                $notToDelete[] = $throttleId;
                $arraySave[$throttleId]['max_count'] = $value;
            } elseif (str_starts_with($key, 'throttle_period_') && preg_match('/^throttle_period_(\d+)$/', $key, $matches)) {
                $throttleId = $matches[1];
                $arraySave[$throttleId]['period'] = $value;
            }
        }

        foreach ($arraySave as $throttleData) {
            if (!ctype_digit((string)$throttleData['max_count']) || !in_array($throttleData['period'], $allowedPeriods, true)) {
                $this->db->transRollback();

                return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.saved_unsuccessfully')]);
            }
        }

        foreach ($arraySave as $throttleId => $throttleData) {
            $savedThrottleId = $this->jobThrottle->saveValue($throttleData, $throttleId);
            $notToDelete[] = (string)$savedThrottleId;
        }

        // All throttles not available in post will be deleted now
        $deletedThrottles = $this->jobThrottle->getAll()->getResultArray();

        foreach ($deletedThrottles as $throttle) {
            if (!in_array($throttle['throttle_id'], $notToDelete)) {
                $this->jobThrottle->delete($throttle['throttle_id']);
            }
        }

        $this->db->transComplete();

        $success = $this->db->transStatus();

        return $this->response->setJSON(['success' => $success, 'message' => lang('Jobs.saved_' . ($success ? '' : 'un') . 'successfully')]);
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function getThrottles(): string
    {
        $throttles = $this->jobThrottle->getAll()->getResultArray();

        return view('partial/job_throttles', ['throttles' => $throttles]);
    }

    /**
     * Synchronously drains queues, bounded by jobs_manual_max_seconds so the
     * request can't hang indefinitely. When the scope is 'selected', drains only
     * the queue names posted from the Utilities tab's queue multiselect;
     * otherwise drains all core queues.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postProcessJobs(): ResponseInterface
    {
        $coreQueues = config(JobsConfig::class)->coreQueues;

        if ($this->request->getPost('scope') !== 'selected') {
            return $this->runWorker($coreQueues);
        }

        $selectedQueues = array_intersect($this->request->getPost('selected_jobs') ?? [], $coreQueues);

        if ($selectedQueues === []) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.no_queues_selected')]);
        }

        return $this->runWorker(array_values($selectedQueues));
    }

    /**
     * @param string[] $queues
     */
    private function runWorker(array $queues): ResponseInterface
    {
        $maxSeconds = (int)($this->config['jobs_manual_max_seconds'] ?? config(JobsConfig::class)->manualMaxSeconds);

        $worker = new BoundedQueueWorker($queues, microtime(true) + $maxSeconds);
        $worker->run();

        return $this->response->setJSON([
            'success' => true,
            'message' => lang('Jobs.processed_jobs_result', [$worker->getProcessedCount(), $worker->getFailedCount()]),
        ]);
    }
}
