<?php

namespace App\Controllers;

use App\Jobs\BoundedQueueWorker;
use App\Models\Appconfig;
use App\Models\JobQueueManage;
use App\Models\JobThrottle;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use CodeIgniter\Queue\Entities\QueueJob;
use CodeIgniter\Queue\Enums\Status;
use CodeIgniter\Queue\Models\QueueJobFailedModel;
use CodeIgniter\Queue\Models\QueueJobModel;
use Config\Database;
use Config\Jobs as JobsConfig;
use ReflectionException;

class Jobs extends Secure_Controller
{
    private BaseConnection $db;
    private Appconfig $appconfig;
    private JobThrottle $jobThrottle;
    private JobQueueManage $jobQueueManage;
    private array $config;

    public function __construct()
    {
        parent::__construct('jobs');

        $this->db = Database::connect();
        $this->appconfig = model(Appconfig::class);
        $this->jobThrottle = model(JobThrottle::class);
        $this->jobQueueManage = model(JobQueueManage::class);
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
        $data['table_headers'] = get_jobs_manage_table_headers();

        return view('jobs/manage', $data);
    }

    /**
     * Renders the edit modal for a single job (payload JSON, priority,
     * available_at). Used in app/Views/jobs/job_edit.php.
     *
     * @return string|ResponseInterface
     * @noinspection PhpUnused
     */
    public function getView(string $uid)
    {
        [$source, $id] = $this->splitUid($uid);

        if ($source === null || $source === 'failed') {
            return $this->response->setStatusCode(404);
        }

        $job = model(QueueJobModel::class)->find($id);

        if ($job === null) {
            return $this->response->setStatusCode(404);
        }

        $data['uid'] = $uid;
        $data['queue'] = $job->queue;
        $data['priority'] = $job->priority;
        $data['payload'] = json_encode($job->payload, JSON_PRETTY_PRINT);
        $data['available_at'] = $job->available_at->format('Y-m-d\TH:i');
        $data['priorities'] = config('Queue')->queuePriorities[$job->queue] ?? ['high', 'normal', 'low'];

        return view('jobs/job_edit', $data);
    }

    /**
     * Saves the edited payload/priority/available_at for a pending or
     * reserved job.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postSave(string $uid): ResponseInterface
    {
        [$source, $id] = $this->splitUid($uid);

        if ($source === null || $source === 'failed') {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
        }

        $queueJobModel = model(QueueJobModel::class);
        $job = $queueJobModel->find($id);

        if ($job === null) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
        }

        $payloadJson = $this->request->getPost('payload');
        $payload = json_decode((string)$payloadJson, true);

        if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_payload_json')]);
        }

        $priority = $this->request->getPost('priority');
        $allowedPriorities = config('Queue')->queuePriorities[$job->queue] ?? ['high', 'normal', 'low'];

        if (!in_array($priority, $allowedPriorities, true)) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_priority')]);
        }

        $availableAt = $this->request->getPost('available_at');
        $availableAtTime = $availableAt ? new Time($availableAt) : $job->available_at;

        $job->payload = $payload;
        $job->priority = $priority;
        $job->available_at = $availableAtTime;

        $success = $queueJobModel->save($job);

        return $this->response->setJSON([
            'success' => $success,
            'message' => lang($success ? 'Jobs.saved_successfully' : 'Jobs.saved_unsuccessfully'),
            'id'      => $uid,
        ]);
    }

    /**
     * Returns jobs table data rows. This will be called with AJAX.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function getSearch(): ResponseInterface
    {
        $search = $this->request->getGet('search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $limit = (int)$this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = (int)$this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort = $this->sanitizeSortColumn(job_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'date');
        $order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'desc';
        $queues = $this->request->getGet('queues', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];

        $jobs = $this->jobQueueManage->search($search, $queues, $limit, $offset, $sort, $order);
        $total_rows = $this->jobQueueManage->getFoundRows($search, $queues);

        $data_rows = [];

        foreach ($jobs as $job) {
            $job->payload = $this->jobQueueManage->decodePayload($job);
            $data_rows[] = get_job_data_row($job);
        }

        return $this->response->setJSON(['total' => $total_rows, 'rows' => $data_rows]);
    }

    /**
     * Deletes one or more jobs. Reserved (in-progress) jobs cannot be
     * deleted.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postDelete(): ResponseInterface
    {
        $uids = $this->request->getPost('ids') ?? [];

        $queueJobModel = model(QueueJobModel::class);
        $queueJobFailedModel = model(QueueJobFailedModel::class);

        $deleted = 0;
        $blocked = 0;

        foreach ($uids as $uid) {
            [$source, $id] = $this->splitUid($uid);

            if ($source === null) {
                continue;
            }

            if ($source === 'failed') {
                $deleted += $queueJobFailedModel->delete($id) ? 1 : 0;

                continue;
            }

            $job = $queueJobModel->find($id);

            if ($job === null) {
                continue;
            }

            if ($job->status === Status::RESERVED->value) {
                $blocked++;

                continue;
            }

            $deleted += $queueJobModel->delete($id) ? 1 : 0;
        }

        if ($blocked > 0 && $deleted === 0) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.delete_blocked_in_progress')]);
        }

        $message = lang('Jobs.successful_deleted', [$deleted]);

        if ($blocked > 0) {
            $message .= ' ' . lang('Jobs.delete_blocked_in_progress');
        }

        return $this->response->setJSON(['success' => true, 'message' => $message]);
    }

    /**
     * Processes (or requeues then processes) a single job. Used by the
     * Manage tab's per-row play/requeue icon.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postProcessJob(): ResponseInterface
    {
        $uid = $this->request->getPost('id');
        [$source, $id] = $this->splitUid($uid);

        if ($source === null) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
        }

        if ($source === 'reserved') {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.job_in_progress')]);
        }

        $queueJobModel = model(QueueJobModel::class);

        if ($source === 'failed') {
            $work = $this->requeueFailedJob($id);

            if ($work === null) {
                return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
            }
        } else {
            $work = $queueJobModel->find($id);

            if ($work === null) {
                return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
            }
        }

        $maxSeconds = (int)($this->config['jobs_manual_max_seconds'] ?? config(JobsConfig::class)->manualMaxSeconds);
        $worker = new BoundedQueueWorker([$work->queue], microtime(true) + $maxSeconds);
        $worker->runOne($work);

        $success = $worker->getProcessedCount() > 0;

        return $this->response->setJSON([
            'success' => $success,
            'message' => lang($success ? 'Jobs.job_processed' : 'Jobs.job_failed'),
        ]);
    }

    /**
     * Moves a failed job back into queue_jobs as pending so it can be
     * reprocessed, returning the freshly-inserted QueueJob.
     */
    private function requeueFailedJob(int $id): ?QueueJob
    {
        $queueJobFailedModel = model(QueueJobFailedModel::class);
        $failedJob = $queueJobFailedModel->find($id);

        if ($failedJob === null) {
            return null;
        }

        $queueJobModel = model(QueueJobModel::class);
        $newId = $queueJobModel->insert(new QueueJob([
            'queue'        => $failedJob->queue,
            'payload'      => $failedJob->payload,
            'priority'     => $failedJob->priority,
            'status'       => Status::PENDING->value,
            'attempts'     => 0,
            'available_at' => Time::now(),
        ]));

        if (!$newId) {
            return null;
        }

        $queueJobFailedModel->delete($id);

        return $queueJobModel->find($newId);
    }

    /**
     * @param mixed $uid
     * @return array{0: string|null, 1: int}
     */
    private function splitUid($uid): array
    {
        if (!is_string($uid) || !str_contains($uid, ':')) {
            return [null, 0];
        }

        [$source, $id] = explode(':', $uid, 2);

        if (!in_array($source, ['pending', 'reserved', 'failed'], true) || !ctype_digit($id)) {
            return [null, 0];
        }

        return [$source, (int)$id];
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
