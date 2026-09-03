<?php

namespace Tests\Support;

use App\Models\Employee;
use CodeIgniter\Config\Services;

trait AdminAuthTrait
{
    protected function loginAsAdminWithGrants(array $permissionIds, string $menuGroup = 'office'): int
    {
        $personData = [
            'first_name'   => 'Test',
            'last_name'    => 'Admin',
            'email'        => 'admin-' . uniqid() . '@test.com',
            'phone_number' => '555-0100',
        ];

        $employeeData = [
            'username'      => 'admin_' . uniqid(),
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english',
        ];

        $grantsData = array_map(
            static fn (string $permissionId) => ['permission_id' => $permissionId, 'menu_group' => $menuGroup],
            $permissionIds
        );

        $employeeModel = model(Employee::class);
        $employeeModel->save_employee($personData, $employeeData, $grantsData, NEW_ENTRY);

        $personId = $personData['person_id'];

        $session = Services::session();
        $session->destroy();
        $session->set('person_id', $personId);
        $session->set('menu_group', $menuGroup);

        $this->withSession($session->get());

        return $personId;
    }
}
