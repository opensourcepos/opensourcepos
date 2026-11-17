<?php

namespace App\Controllers;

use App\Jobs\BoundedQueueWorker;
use App\Models\Appconfig;
use App\Models\ImportBatch;
use App\Models\JobQueueManage;
use App\Models\JobThrottle;
use App\Models\QueueJobFailedModel;
use App\Models\QueueJobModel;
use App\Models\QueueJobPausedModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use CodeIgniter\Queue\Entities\QueueJob;
use CodeIgniter\Queue\Enums\Status;
use Config\Jobs as JobsConfig;
use Exception;
use ReflectionException;

class Jobs extends Secure_Controller
{
    private Appconfig $appconfig;
    private JobThrottle $jobThrottle;
    private JobQueueManage $jobQueueManage;
    private QueueJobModel $queueJobModel;
    private QueueJobPausedModel $queueJobPaused;
    private QueueJobFailedModel $queueJobFailedModel;
    private array $config;

    public function __construct()
    {
        parent::__construct('jobs');

        $this->appconfig = model(Appconfig::class);
        $this->jobThrottle = model(JobThrottle::class);
        $this->jobQueueManage = model(JobQueueManage::class);
        $this->queueJobModel = model(QueueJobModel::class);
        $this->queueJobPaused = model(QueueJobPausedModel::class);
        $this->queueJobFailedModel = model(QueueJobFailedModel::class);
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
        $data['coreQueues'] = $data['queues'];
        $data['table_headers'] = get_jobs_manage_table_headers();
        $data['batch_table_headers'] = get_batch_table_headers();

        return view('jobs/manage', $data);
    }

    /**
     * Renders the edit modal for a single job (payload JSON, priority,
     * available_at). Used in app/Views/jobs/job_edit.php.
     *
     * @return string|ResponseInterface
     * @noinspection PhpUnused
     */
    public function getEditJob(string $source, string $id)
    {
        [$source, $id] = $this->splitUid("$source:$id");

        if ($source === null || $source === 'failed') {
            return $this->response->setStatusCode(404);
        }

        $job = $this->queueJobModel->find($id);

        if ($job === null) {
            return $this->response->setStatusCode(404);
        }

        $data['uid'] = "$source:$id";
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
    public function postSaveJob(string $source, string $id): ResponseInterface
    {
        $uid = "$source:$id";
        [$source, $id] = $this->splitUid($uid);

        if ($source === null || $source === 'failed') {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
        }

        $job = $this->queueJobModel->find($id);

        if ($job === null) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
        }

        $payloadJson = $this->request->getPost('payload');
        $payload = json_decode((string)$payloadJson, true);

        if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_payload_json')]);
        }

        $jobKey = $payload['job'] ?? null;

        if (!is_string($jobKey) || !array_key_exists($jobKey, config('Queue')->jobHandlers)) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_payload_job')]);
        }

        if (!isset($payload['data']) || !is_array($payload['data'])) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_payload_data')]);
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

        $success = $this->queueJobModel->save($job);

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
        $statuses = $this->request->getGet('statuses', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];

        $jobs = $this->jobQueueManage->search($search, $queues, $limit, $offset, $sort, $order, false, $statuses);
        $total_rows = $this->jobQueueManage->getFoundRows($search, $queues, $statuses);

        $data_rows = [];

        foreach ($jobs as $job) {
            $job->payload = $this->jobQueueManage->decodePayload($job);
            $data_rows[] = get_job_data_row($job);
        }

        return $this->response->setJSON(['total' => $total_rows, 'rows' => $data_rows]);
    }

    /**
     * Returns batch table data rows for the Batches tab.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function getSearchBatches(): ResponseInterface
    {
        $search = $this->request->getGet('search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $limit = (int)$this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = (int)$this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort = $this->sanitizeSortColumn(batch_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'date');
        $order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'desc';
        $statuses = $this->request->getGet('statuses', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];

        $allowedStatuses = ['pending', 'processing', 'completed', 'partial'];
        $statuses = array_values(array_intersect((array)$statuses, $allowedStatuses));

        $sortable = ['type', 'total', 'completed', 'failed', 'status', 'date'];
        $sort = in_array($sort, $sortable, true) ? $sort : 'created_at';
        $sort = $sort === 'date' ? 'created_at' : $sort;
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $batchModel = model(ImportBatch::class);

        $query = $batchModel->orderBy($sort, $order);

        if ($search !== '') {
            $query->groupStart()
                ->like('id', $search)
                ->orLike('type', $search)
                ->orLike('status', $search)
                ->groupEnd();
        }

        if ($statuses !== []) {
            $query->whereIn('status', $statuses);
        }

        $totalRows = $query->countAllResults(false);

        if ($limit > 0) {
            $query->limit($limit, $offset);
        }

        $batches = $query->findAll();
        $pausedBatchIds = array_flip($this->queueJobPaused->getBatchIdsWithPausedJobs(array_column($batches, 'id')));
        $dataRows = array_map(
            static fn (array $batch) => get_batch_data_row($batch, isset($pausedBatchIds[$batch['id']])),
            $batches
        );

        return $this->response->setJSON(['total' => $totalRows, 'rows' => $dataRows]);
    }

    /**
     * Deletes a batch and all its associated jobs (pending and paused only;
     * reserved jobs are left to complete).
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postDeleteBatch(): ResponseInterface
    {
        [$batchId, $batchModel, $errorResponse] = $this->requireBatch();

        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $this->queueJobModel->db->transStart();

        $this->queueJobModel->deletePendingByBatchId($batchId);
        $this->queueJobPaused->deleteByBatchId($batchId);
        $this->queueJobFailedModel->deleteByBatchId($batchId);
        $batchModel->delete($batchId);

        $this->queueJobModel->db->transComplete();

        $success = $this->queueJobModel->db->transStatus();

        return $this->response->setJSON([
            'success' => $success,
            'message' => lang($success ? 'Jobs.batch_deleted' : 'Jobs.batch_delete_failed'),
        ]);
    }

    /**
     * Pauses all pending jobs for a batch by moving them to queue_jobs_paused.
     * Reserved jobs are left alone.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postPauseBatch(): ResponseInterface
    {
        [$batchId, , $errorResponse] = $this->requireBatch();

        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $this->queueJobModel->db->transStart();

        $rows = $this->queueJobModel->getPendingForUpdateByBatchId($batchId);
        $this->moveToPaused($rows);

        $this->queueJobModel->db->transComplete();

        $success = $this->queueJobModel->db->transStatus();

        return $this->response->setJSON([
            'success' => $success,
            'message' => lang($success ? 'Jobs.batch_paused' : 'Jobs.batch_pause_failed', ['count' => count($rows)]),
        ]);
    }

    /**
     * Resumes all paused jobs for a batch by moving them back to queue_jobs.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postResumeBatch(): ResponseInterface
    {
        [$batchId, , $errorResponse] = $this->requireBatch();

        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $this->queueJobModel->db->transStart();

        $rows = $this->queueJobPaused->getByBatchId($batchId);
        $this->moveToPending($rows);

        $this->queueJobModel->db->transComplete();

        $success = $this->queueJobModel->db->transStatus();

        return $this->response->setJSON([
            'success' => $success,
            'message' => lang($success ? 'Jobs.batch_resumed' : 'Jobs.batch_resume_failed', ['count' => count($rows)]),
        ]);
    }

    /**
     * Purges a completed or partial batch: deletes the batch record and all
     * associated job records. Only eligible for completed/partial batches.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postPurgeBatch(): ResponseInterface
    {
        [$batchId, $batchModel, $errorResponse] = $this->requireBatch();

        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $batch = $batchModel->find($batchId);

        if (!in_array($batch['status'], ['completed', 'partial'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.batch_purge_not_eligible')]);
        }

        $this->queueJobModel->db->transStart();

        $this->queueJobFailedModel->deleteByBatchId($batchId);
        $batchModel->delete($batchId);

        $this->queueJobModel->db->transComplete();

        $success = $this->queueJobModel->db->transStatus();

        return $this->response->setJSON([
            'success' => $success,
            'message' => lang($success ? 'Jobs.batch_purged' : 'Jobs.batch_purge_failed'),
        ]);
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

        $deleted = 0;
        $blocked = 0;

        foreach ($uids as $uid) {
            [$source, $id] = $this->splitUid($uid);

            if ($source === null) {
                continue;
            }

            [$didDelete, $didBlock] = $this->deleteOneJob($source, $id);
            $deleted += $didDelete;
            $blocked += $didBlock;
        }

        if ($blocked > 0 && $deleted === 0) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.delete_blocked_in_progress')]);
        }

        $message = lang('Jobs.successful_deleted', ['count' => $deleted]);

        if ($blocked > 0) {
            $message .= ' ' . lang('Jobs.delete_blocked_in_progress');
        }

        return $this->response->setJSON(['success' => true, 'message' => $message]);
    }

    private function deleteOneJob(string $source, int $id): array
    {
        if ($source === 'failed') {
            return [$this->queueJobFailedModel->delete($id) ? 1 : 0, 0];
        }

        if ($source === 'paused') {
            return [$this->queueJobPaused->delete($id) ? 1 : 0, 0];
        }

        $this->queueJobModel->db->transStart();

        $rows = $this->queueJobModel->getPendingForUpdateByIdOrQueues($id, null);

        if ($rows !== []) {
            $this->queueJobModel->delete($id);
            $this->accountBatchCancellation($rows[0]['batch_id'] ?? null);
        }

        $this->queueJobModel->db->transComplete();

        if ($rows === []) {
            return [0, $this->queueJobModel->find($id) !== null ? 1 : 0];
        }

        return [$this->queueJobModel->db->transStatus() ? 1 : 0, 0];
    }

    /**
     * Processes (or requeues then processes) a single job. Used by the
     * Manage tab's per-row play/requeue icon.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     * @throws ReflectionException
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

        if ($source === 'failed') {
            $work = $this->requeueFailedJob($id);

            if ($work === null) {
                return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
            }
        } else {
            $work = $this->queueJobModel->find($id);

            if ($work === null) {
                return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
            }
        }

        if (!$this->queueJobModel->claimJob($work->id)) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.job_in_progress')]);
        }

        $work->status = Status::RESERVED->value;

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
     * @throws ReflectionException
     * @throws Exception
     */
    private function requeueFailedJob(int $id): ?QueueJob
    {
        $now = Time::now()->timestamp;
        $inserted = false;
        $newId = 0;

        $this->queueJobModel->db->transStart();

        $failedJob = $this->queueJobFailedModel->getForUpdateById($id);

        if ($failedJob !== null) {
            $inserted = $this->queueJobModel->insertRows([[
                'queue'        => $failedJob['queue'],
                'payload'      => $failedJob['payload'],
                'priority'     => $failedJob['priority'],
                'status'       => Status::PENDING->value,
                'attempts'     => 0,
                'available_at' => $now,
                'created_at'   => $now,
                'batch_id'     => $failedJob['batch_id'] ?? null,
                'context_id'   => $failedJob['context_id'] ?? null,
            ]]);

            $newId = $inserted ? $this->queueJobModel->db->insertID() : 0;
        }

        if ($inserted) {
            $this->queueJobFailedModel->delete($id);

            if (!empty($failedJob['batch_id'])) {
                model(ImportBatch::class)->releaseFailed($failedJob['batch_id']);
            }
        }

        $this->queueJobModel->db->transComplete();

        if (!$inserted || !$this->queueJobModel->db->transStatus()) {
            return null;
        }

        /** @var QueueJob|null $job */
        $job = $this->queueJobModel->find($newId);

        return $job;
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

        if (!in_array($source, ['pending', 'reserved', 'failed', 'paused'], true) || !ctype_digit($id)) {
            return [null, 0];
        }

        return [$source, (int)$id];
    }

    /**
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postResumeJobs(): ResponseInterface
    {
        $coreQueues = config(JobsConfig::class)->coreQueues;

        if ($this->request->getPost('scope') !== 'selected') {
            return $this->resumeJobs(null, $coreQueues);
        }

        $selectedQueues = $this->resolveSelectedQueues($coreQueues);

        if ($selectedQueues === []) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.no_queues_selected')]);
        }

        return $this->resumeJobs(null, $selectedQueues);
    }

    /**
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postResumeJob(): ResponseInterface
    {
        $uid = $this->request->getPost('id');
        [$source, $id] = $this->splitUid($uid);

        if ($source !== 'paused') {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
        }

        return $this->resumeJobs($id);
    }

    private function resumeJobs(?int $id, ?array $queues = null): ResponseInterface
    {
        $this->queueJobModel->db->transStart();

        $rows = $this->queueJobPaused->getByIdOrQueues($id, $queues);
        $this->moveToPending($rows);

        $this->queueJobModel->db->transComplete();

        $success = $this->queueJobModel->db->transStatus();

        return $this->response->setJSON([
            'success' => $success,
            'message' => lang($success ? 'Jobs.resumed_jobs_result' : 'Jobs.resume_jobs_failed', ['count' => count($rows)]),
        ]);
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
            'retry_limit'           => 'required|is_natural_no_zero|less_than_equal_to[255]',
            'auto_purge'            => 'permit_empty|in_list[0,1]',
            'retention_days'        => 'required|is_natural',
            'failed_retention_days' => 'required|is_natural',
        ];

        $messages = [
            'mode'                  => ['in_list' => lang('Jobs.mode_invalid')],
            'web_max_seconds'       => ['is_natural' => lang('Jobs.web_max_seconds_invalid')],
            'task_max_seconds'      => ['is_natural' => lang('Jobs.task_max_seconds_invalid')],
            'retry_limit'           => [
                'is_natural_no_zero'  => lang('Jobs.retry_limit_invalid'),
                'less_than_equal_to' => lang('Jobs.retry_limit_invalid'),
            ],
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

        $this->jobThrottle->db->transStart();

        $notToDelete = [];
        $arraySave = [];

        $coreQueues = config(JobsConfig::class)->coreQueues;

        foreach ($this->request->getPost() as $key => $value) {
            if (str_starts_with($key, 'throttle_count_') && preg_match('/^throttle_count_(\d+)$/', $key, $matches)) {
                $throttleId = $matches[1];
                $notToDelete[] = $throttleId;
                $arraySave[$throttleId]['max_count'] = $value;
            } elseif (str_starts_with($key, 'throttle_period_') && preg_match('/^throttle_period_(\d+)$/', $key, $matches)) {
                $throttleId = $matches[1];
                $arraySave[$throttleId]['period'] = $value;
            } elseif (str_starts_with($key, 'throttle_queue_') && preg_match('/^throttle_queue_(\d+)$/', $key, $matches)) {
                $throttleId = $matches[1];
                $arraySave[$throttleId]['queue'] = $value !== '' ? $value : null;
            }
        }

        foreach ($arraySave as $throttleData) {
            $queue = $throttleData['queue'] ?? null;

            if (
                !ctype_digit((string)$throttleData['max_count'])
                || !in_array($throttleData['period'], $allowedPeriods, true)
                || ($queue !== null && !in_array($queue, $coreQueues, true))
            ) {
                $this->jobThrottle->db->transRollback();

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

        $this->jobThrottle->db->transComplete();

        $success = $this->jobThrottle->db->transStatus();

        return $this->response->setJSON(['success' => $success, 'message' => lang('Jobs.saved_' . ($success ? '' : 'un') . 'successfully')]);
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function getThrottles(): string
    {
        $throttles = $this->jobThrottle->getAll()->getResultArray();
        $coreQueues = config(JobsConfig::class)->coreQueues;

        return view('partial/job_throttles', ['throttles' => $throttles, 'coreQueues' => $coreQueues]);
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

        $selectedQueues = $this->resolveSelectedQueues($coreQueues);

        if ($selectedQueues === []) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.no_queues_selected')]);
        }

        return $this->runWorker($selectedQueues);
    }

    private function runWorker(array $queues): ResponseInterface
    {
        $maxSeconds = (int)($this->config['jobs_manual_max_seconds'] ?? config(JobsConfig::class)->manualMaxSeconds);

        $worker = new BoundedQueueWorker($queues, microtime(true) + $maxSeconds);
        $worker->run();

        return $this->response->setJSON([
            'success' => true,
            'message' => lang('Jobs.processed_jobs_result', ['processed' => $worker->getProcessedCount(), 'failed' => $worker->getFailedCount()]),
        ]);
    }

    /**
     * Moves pending jobs out of queue_jobs into queue_jobs_paused for the
     * given queues (or all core queues), leaving in-flight ('reserved') jobs
     * alone to finish (issue #3833's pause/resume design). When the scope is
     * 'selected', pauses only the queue names posted from the Utilities tab's
     * queue multiselect.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postPauseJobs(): ResponseInterface
    {
        $coreQueues = config(JobsConfig::class)->coreQueues;

        if ($this->request->getPost('scope') !== 'selected') {
            return $this->pauseJobs(null, $coreQueues);
        }

        $selectedQueues = $this->resolveSelectedQueues($coreQueues);

        if ($selectedQueues === []) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.no_queues_selected')]);
        }

        return $this->pauseJobs(null, $selectedQueues);
    }

    /**
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postPauseJob(): ResponseInterface
    {
        $uid = $this->request->getPost('id');
        [$source, $id] = $this->splitUid($uid);

        if ($source !== 'pending') {
            return $this->response->setJSON(['success' => false, 'message' => lang('Jobs.invalid_job')]);
        }

        return $this->pauseJobs($id);
    }

    private function pauseJobs(?int $id, ?array $queues = null): ResponseInterface
    {
        $this->queueJobModel->db->transStart();

        $rows = $this->queueJobModel->getPendingForUpdateByIdOrQueues($id, $queues);
        $this->moveToPaused($rows);

        $this->queueJobModel->db->transComplete();

        $success = $this->queueJobModel->db->transStatus();

        return $this->response->setJSON([
            'success' => $success,
            'message' => lang($success ? 'Jobs.paused_jobs_result' : 'Jobs.pause_jobs_failed', ['count' => count($rows)]),
        ]);
    }

    private function moveToPaused(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $now = Time::now()->timestamp;
        $batchModel = model(ImportBatch::class);

        foreach ($rows as &$row) {
            $row['paused_at'] = $now;
            $row['paused_by'] = 'manual';
            unset($row['status']);
        }
        unset($row);

        $this->queueJobPaused->insertRows($rows);
        $this->queueJobModel->deleteWhereInIds(array_column($rows, 'id'));

        foreach (array_column($rows, 'batch_id') as $batchId) {
            if ($batchId !== null) {
                $batchModel->adjustTotal($batchId, -1);
            }
        }
    }

    private function moveToPending(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $pendingStatus = Status::PENDING->value;

        foreach ($rows as &$row) {
            $row['status'] = $pendingStatus;
            unset($row['paused_at'], $row['paused_by'], $row['batch_id']);
        }
        unset($row);

        $this->queueJobModel->insertRows($rows);
        $this->queueJobPaused->deleteWhereInIds(array_column($rows, 'id'));
    }

    private function accountBatchCancellation(?string $batchId): void
    {
        if ($batchId === null) {
            return;
        }

        model(ImportBatch::class)->adjustTotal($batchId, -1);
    }

    private function resolveSelectedQueues(array $coreQueues): array
    {
        return array_values(array_intersect($this->request->getPost('selected_jobs') ?? [], $coreQueues));
    }

    private function requireBatch(): array
    {
        $batchId    = $this->request->getPost('id');
        $batchModel = model(ImportBatch::class);

        if (empty($batchId)) {
            return [null, $batchModel, $this->response->setJSON(['success' => false, 'message' => lang('Jobs.batch_invalid')])];
        }

        if ($batchModel->find($batchId) === null) {
            return [null, $batchModel, $this->response->setJSON(['success' => false, 'message' => lang('Jobs.batch_not_found')])];
        }

        return [$batchId, $batchModel, null];
    }
}
