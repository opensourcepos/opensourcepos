<?php

namespace App\Controllers;

use App\Libraries\MY_Migration;
use App\Models\Employee;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;
use Config\OSPOS;
use Config\Services;

/**
 * @property employee employee
 */
class Login extends BaseController
{
    public Model $employee;

    /**
     * @return RedirectResponse|ResponseInterface|string
     */
    public function index(): string|RedirectResponse|ResponseInterface
    {
        $this->employee = model(Employee::class);
        if (!$this->employee->is_logged_in()) {
            $migration = new MY_Migration(config('Migrations'));
            $config = config(OSPOS::class)->settings;

            $gcaptchaEnabled = array_key_exists('gcaptcha_enable', $config)
                ? $config['gcaptcha_enable']
                : false;

            $migration->migrateToCI4();

            $currentVersion = MY_Migration::getCurrentVersion();

            if ($currentVersion === null) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Login.migration_error_connection')
                ])->setStatusCode(503);
            }

            $latestVersion = $migration->getLatestMigration();

            $validation = Services::validation();

            $data = [
                'hasErrors'       => false,
                'isNewInstall'   => $currentVersion === 0,
                'isLatest'        => $latestVersion === $currentVersion,
                'latestVersion'   => $latestVersion,
                'gcaptchaEnabled' => $gcaptchaEnabled,
                'config'           => $config,
                'validation'       => $validation
            ];

            if ($this->request->getMethod() !== 'POST') {
                return view('login', $data);
            }

            if (!$data['isLatest'] || $data['isNewInstall']) {
                set_time_limit(3600);

                $migration->setNamespace('App')->latest();
                return redirect()->to('login');
            }

            $rules = ['username' => 'required|login_check[data]'];
            $messages = [
                'username' => [
                    'required'    => lang('Login.required_username'),
                    'login_check' => lang('Login.invalid_username_and_password'),
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $data['has_errors'] = !empty($validation->getErrors());

                return view('login', $data);
            }
        }

        return redirect()->to('home');
    }

    public function migrate(): ResponseInterface
    {
        $currentVersion = MY_Migration::getCurrentVersion();

        if ($currentVersion === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Login.migration_error_connection')
            ])->setStatusCode(503);
        }

        // Only a confirmed empty database counts as a new install with no credentials to check.
        $isNewInstall = $currentVersion === 0;

        if (!$isNewInstall) {
            $rules = ['username' => 'required|login_check[data]'];
            $messages = [
                'username' => [
                    'required'    => lang('Login.required_username'),
                    'login_check' => lang('Login.invalid_username_and_password'),
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Login.invalid_username_and_password')
                ])->setStatusCode(401);
            }
        }

        try {
            $migration = new MY_Migration(config('Migrations'));
            $migration->migrateToCI4();

            set_time_limit(3600);
            $migration->setNamespace('App')->latest();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Migration completed successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Migration failed: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
