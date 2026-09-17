<?php

namespace App\Controllers;

use App\Models\Appconfig;
use App\Models\JobThrottle;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
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
            'mode'            => 'required|in_list[auto,web,manual]',
            'web_max_seconds' => 'required|is_natural'
        ];

        $messages = [
            'mode'            => ['in_list' => lang('Jobs.mode_invalid')],
            'web_max_seconds' => ['is_natural' => lang('Jobs.web_max_seconds_invalid')]
        ];

        if ($response = $this->validateFields($rules, $messages)) {
            return $response;
        }

        $batchSaveData = [
            'jobs_mode'            => $this->request->getPost('mode'),
            'jobs_web_max_seconds' => $this->request->getPost('web_max_seconds', FILTER_SANITIZE_NUMBER_INT)
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
        $allowedPeriods = ['minute', 'hour', 'day', 'month'];

        $this->db->transStart();

        $notToDelete = [];
        $arraySave = [];

        foreach ($this->request->getPost() as $key => $value) {
            if (str_starts_with($key, 'throttle_count_')) {
                $throttleId = preg_replace('/.*?_(\d+)$/', '$1', $key);
                $notToDelete[] = $throttleId;
                $arraySave[$throttleId]['max_count'] = $value;
            } elseif (str_starts_with($key, 'throttle_period_')) {
                $throttleId = preg_replace('/.*?_(\d+)$/', '$1', $key);
                $arraySave[$throttleId]['period'] = $value;
            }
        }

        $success = true;

        foreach ($arraySave as $throttleId => $throttleData) {
            if (!ctype_digit((string)$throttleData['max_count']) || !in_array($throttleData['period'], $allowedPeriods, true)) {
                $success = false;
                continue;
            }

            $this->jobThrottle->saveValue($throttleData, $throttleId);
        }

        // All throttles not available in post will be deleted now
        $deletedThrottles = $this->jobThrottle->getAll()->getResultArray();

        foreach ($deletedThrottles as $throttle) {
            if (!in_array($throttle['throttle_id'], $notToDelete)) {
                $this->jobThrottle->delete($throttle['throttle_id']);
            }
        }

        $this->db->transComplete();

        $success = $success && $this->db->transStatus();

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
     * Stub for Phase 1 scaffolding. Real processing is wired up in a later phase.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postProcessAllJobs(): ResponseInterface
    {
        return $this->response->setJSON(['success' => false, 'stub' => true, 'message' => lang('Jobs.not_yet_implemented')]);
    }

    /**
     * Stub for Phase 1 scaffolding. Real processing is wired up in a later phase.
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postProcessSelectedJobs(): ResponseInterface
    {
        return $this->response->setJSON(['success' => false, 'stub' => true, 'message' => lang('Jobs.not_yet_implemented')]);
    }
}
