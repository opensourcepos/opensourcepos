<?php

namespace App\Controllers;

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
     * Load "change employee password" form
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function getChangePassword(int $employeeId = DEFAULT_EMPLOYEE_ID): string
    {
        $loggedInEmployee = $this->employee->get_logged_in_employee_info();
        $currentPersonId = $loggedInEmployee->person_id;

        $employeeId = $employeeId === DEFAULT_EMPLOYEE_ID ? $currentPersonId : $employeeId;

        if (!$this->employee->can_modify_employee($employeeId, $currentPersonId)) {
            header('Location: ' . base_url('no_access/home/home'));
            exit();
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
    public function postSave(int $employeeId = DEFAULT_EMPLOYEE_ID): ResponseInterface
    {
        $currentUser = $this->employee->get_logged_in_employee_info();

        $employeeId = $employeeId === DEFAULT_EMPLOYEE_ID ? $currentUser->person_id : $employeeId;

        if (!$this->employee->can_modify_employee($employeeId, $currentUser->person_id)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => lang('Employees.unauthorized_modify')
            ]);
        }

        if (!empty($this->request->getPost('current_password')) && $employeeId != DEFAULT_EMPLOYEE_ID) {
            if ($this->employee->check_password($this->request->getPost('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS), $this->request->getPost('current_password'))) {
                // Validate password length BEFORE hashing
                $new_password = $this->request->getPost('password');
                
                if (strlen($new_password) < 8) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => lang('Employees.password_minlength'),
                        'id'      => DEFAULT_EMPLOYEE_ID
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
                        'id'      => DEFAULT_EMPLOYEE_ID
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => lang('Employees.current_password_invalid'),
                    'id'      => DEFAULT_EMPLOYEE_ID
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => lang('Employees.current_password_invalid'),
                'id'      => DEFAULT_EMPLOYEE_ID
            ]);
        }
    }
}