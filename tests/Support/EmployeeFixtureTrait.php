<?php

namespace Tests\Support;

use App\Models\Employee;

/**
 * Shared factory for creating employees in test fixtures.
 *
 * All employees created via this trait route through
 * {@see Employee::save_employee()}, which is the same code path the
 * application itself uses. That keeps fixtures exercising the real
 * validation and grant-handling logic instead of raw DB inserts.
 *
 * Every employee gets a unique email and username by default (uniqueness
 * required because tests do not always run against a freshly-reset DB —
 * see opensourcepos/opensourcepos#4626 for the root-cause history).
 *
 * Usage:
 *
 *   use Tests\Support\EmployeeFixtureTrait;
 *
 *   class FooTest extends CIUnitTestCase
 *   {
 *       use EmployeeFixtureTrait;
 *
 *       public function testSomething(): void
 *       {
 *           $personId = $this->createEmployee(
 *               first_name: 'Cashier',
 *               last_name:  'NoReports',
 *               email:      'cashier.' . uniqid() . '@test.com',
 *               username:   'cashier_' . uniqid(),
 *               grants: [
 *                   ['permission_id' => 'sales', 'menu_group' => 'home'],
 *               ],
 *           );
 *       }
 *   }
 */
trait EmployeeFixtureTrait
{
    /**
     * Create a new employee (person + employee row + grants) via the
     * application's own code path ({@see Employee::save_employee()}).
     *
     * @param string $first_name
     * @param string $last_name
     * @param null|string $email   unique email (auto-generated if omitted)
     * @param null|string $username unique username (auto-generated if omitted)
     * @param null|string $phone_number
     * @param array $grants list of ['permission_id' => ..., 'menu_group' => ...]
     * @param array $employee extra ospos_employees row overrides
     *
     * @return int the new person_id
     */
    protected function createEmployee(
        string $first_name = 'Temp',
        string $last_name = 'Employee',
        ?string $email = null,
        ?string $username = null,
        ?string $phone_number = null,
        array $grants = [],
        array $employee = []
    ): int {
        if ($email === null) {
            $email = 'employee.' . uniqid() . '@test.com';
        }
        if ($username === null) {
            $username = 'employee_' . uniqid();
        }
        if ($phone_number === null) {
            $phone_number = '';
        }

        $personData = [
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'email'        => $email,
            'phone_number' => $phone_number,
        ];

        $employeeData = array_merge([
            'username'      => $username,
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english',
        ], $employee);

        $model = model(Employee::class);
        $this->assertTrue(
            $model->save_employee($personData, $employeeData, $grants, NEW_ENTRY),
            'createEmployee: save_employee() failed'
        );
        $this->assertArrayHasKey('person_id', $personData);

        return (int) $personData['person_id'];
    }

    /**
     * Variant of {@see self::createEmployee()} for tests that expect the
     * save to fail (e.g. new-employee creation when DISALLOW_GRANT_CHANGE
     * is true). Returns the raw save_employee() result without asserting.
     */
    protected function createEmployeeExpectingFailure(
        string $first_name = 'Rejected',
        string $last_name = 'Tester',
        ?string $email = null,
        ?string $username = null,
        ?string $phone_number = null,
        array $grants = [],
        array $employee = []
    ): bool {
        if ($email === null) {
            $email = 'employee.' . uniqid() . '@test.com';
        }
        if ($username === null) {
            $username = 'employee_' . uniqid();
        }
        if ($phone_number === null) {
            $phone_number = '';
        }

        $personData = [
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'email'        => $email,
            'phone_number' => $phone_number,
        ];
        $employeeData = array_merge([
            'username'      => $username,
            'password'      => password_hash('password123', PASSWORD_DEFAULT),
            'hash_version'  => 2,
            'language_code' => 'en',
            'language'      => 'english',
        ], $employee);

        return model(Employee::class)->save_employee($personData, $employeeData, $grants, NEW_ENTRY);
    }
}
