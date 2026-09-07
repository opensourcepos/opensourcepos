<?php

namespace Tests\Support;

use Config\Database;

trait EmployeeFixtureTrait
{
    protected function createEmployee(): int
    {
        $db = Database::connect();

        $db->table('people')->insert([
            'first_name'   => 'Test',
            'last_name'    => 'Employee',
            'phone_number' => '555-0200',
            'email'        => 'employee-' . uniqid() . '@test.com',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ]);
        $personId = (int) $db->insertID();

        $db->table('employees')->insert([
            'username'  => 'employee_' . uniqid(),
            'password'  => password_hash('password123', PASSWORD_DEFAULT),
            'person_id' => $personId,
        ]);

        return $personId;
    }
}
