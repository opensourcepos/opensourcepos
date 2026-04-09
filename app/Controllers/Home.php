<?php

namespace App\Controllers;

use App\Libraries\MY_Migration;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends Secure_Controller
{
    public function __construct()
    {
        parent::__construct('home', null, 'home');
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        $logged_in = $this->employee->is_logged_in();
        return view('home/home');
    }

    /**
     * Logs the currently logged in employee out of the system.  Used in app/Views/partial/header.php
     *
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    public function getLogout(): RedirectResponse
    {
        $this->employee->logout();
        return redirect()->to('login');
    }

    /**
     * Load the "change employee password" form
     *
     * @param int $employeeId
     * @return ResponseInterface|string
     */
    public function getChangePassword(int $employeeId = NEW_ENTRY): ResponseInterface|string
    {
        $loggedInEmployee = $this->employee->get_logged_in_employee_info();
        $currentPersonId = $loggedInEmployee->person_id;

        $employeeId = $employeeId === NEW_ENTRY ? $currentPersonId : $employeeId;

        if (!$this->employee->isAdmin($currentPersonId) && $employeeId !== $currentPersonId) {
            return $this->response->setStatusCode(403)->setBody(lang('Employees.unauthorized_modify'));
        }

        $person_info = $this->employee->get_info($employeeId);
        foreach (get_object_vars($person_info) as $property => $value) {
            $person_info->$property = $value;
        }
        $data['person_info'] = $person_info;

        return view('home/form_change_password', $data);
    }

    /**
     * Change employee password
     *
     * @return ResponseInterface
     */
    public function postSave(int $employeeId = NEW_ENTRY): ResponseInterface
    {
        $currentUser = $this->employee->get_logged_in_employee_info();

        $employeeId = $employeeId === NEW_ENTRY ? $currentUser->person_id : $employeeId;

        if (!$this->employee->isAdmin($currentUser->person_id) && $employeeId !== $currentUser->person_id) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => lang('Employees.unauthorized_modify')
            ]);
        }

        if (!empty($this->request->getPost('current_password')) && $employeeId != NEW_ENTRY) {
            if ($this->employee->check_password($this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS), $this->request->getPost('current_password'))) {
                // Validate password length BEFORE hashing
                $new_password = $this->request->getPost('password');

                if (strlen($new_password) < 8) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => lang('Employees.password_minlength'),
                        'id'      => NEW_ENTRY
                    ]);
                }

                $employee_data = [
                    'username'     => $this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                    'password'     => password_hash($new_password, PASSWORD_DEFAULT),
                    'hash_version' => 2
                ];

                if ($this->employee->change_password($employee_data, $employeeId)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => lang('Employees.successful_change_password'),
                        'id'      => $employeeId
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => lang('Employees.unsuccessful_change_password'),
                        'id'      => NEW_ENTRY
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Employees.current_password_invalid'),
                    'id'      => NEW_ENTRY
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Employees.current_password_invalid'),
                'id'      => NEW_ENTRY
            ]);
        }
    }
}
