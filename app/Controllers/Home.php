<?php

namespace App\Controllers;

use App\Libraries\MY_Migration;
use App\Models\Employee;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends Secure_Controller
{
    public function __construct()
    {
        $methodName = strtolower(rawurldecode(service('request')->getUri()->getSegment(2)));

        if ($methodName === 'logout') {
            $this->employee = model(Employee::class);
            return;
        }

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

    public function postLogout(): RedirectResponse
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
        $currentPersonId = (int) $loggedInEmployee->person_id;

        $employeeId = $employeeId === NEW_ENTRY ? $currentPersonId : $employeeId;

        if (!$this->employee->isAdmin($currentPersonId) && $employeeId !== $currentPersonId) {
            return $this->response->setStatusCode(403)->setBody(lang('Employees.unauthorized_modify'));
        }

        $person_info = $this->employee->getInfo($employeeId);
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
        $currentPersonId = (int) $currentUser->person_id;

        $employeeId = $employeeId === NEW_ENTRY ? $currentPersonId : $employeeId;

        if (!$this->employee->isAdmin($currentPersonId) && $employeeId !== $currentPersonId) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => lang('Employees.unauthorized_modify')
            ]);
        }

        if (!empty($this->request->getPost('current_password')) && $employeeId !== NEW_ENTRY) {
            $currentEmployee = $this->employee->getInfo($currentPersonId);

            if ($this->employee->check_password($currentEmployee->username, $this->request->getPost('current_password'))) {
                // Validate password length BEFORE hashing
                $newPassword = $this->request->getPost('password');

                if (strlen($newPassword) < 8) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => lang('Employees.password_minlength'),
                        'id'      => NEW_ENTRY
                    ]);
                }

                $employeeData = [
                    'password'     => password_hash($newPassword, PASSWORD_DEFAULT),
                    'hash_version' => 2
                ];

                if ($this->employee->change_password($employeeData, $employeeId)) {
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
