<?php

namespace Tests\Models;

use App\Models\Customer;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\ConcurrentDbRaceTrait;

/**
 * Regression tests for GHSA-995p-52qw-5hh2: adjustRewardPoints() must apply
 * its balance check and its write in a single atomic UPDATE, so that two
 * concurrent reward-point spends against the same customer can never both
 * read the same stale balance and double-spend it.
 */
class CustomerRewardPointsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use ConcurrentDbRaceTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createCustomerWithPoints(int $points): int
    {
        $db = \Config\Database::connect();

        $db->table('people')->insert([
            'first_name'   => 'Reward',
            'last_name'    => 'Customer',
            'phone_number' => '555-0100',
            'email'        => 'reward-customer-' . uniqid() . '@test.com',
            'address_1'    => '',
            'address_2'    => '',
            'city'         => '',
            'state'        => '',
            'zip'          => '',
            'country'      => '',
            'comments'     => '',
        ]);
        $personId = (int) $db->insertID();

        $db->table('customers')->insert([
            'person_id'   => $personId,
            'employee_id' => 1,
            'points'      => $points,
        ]);

        return $personId;
    }

    protected function getCustomerPoints(int $personId): int
    {
        $row = \Config\Database::connect()->table('customers')
            ->where('person_id', $personId)
            ->get()
            ->getRow();

        return (int) $row->points;
    }

    public function testAdjustRewardPointsIncrementSucceeds(): void
    {
        $personId      = $this->createCustomerWithPoints(10);
        $customerModel = model(Customer::class);

        $result = $customerModel->adjustRewardPoints($personId, 5);

        $this->assertTrue($result);
        $this->assertSame(15, $this->getCustomerPoints($personId));
    }

    public function testAdjustRewardPointsDecrementSucceedsWithSufficientPoints(): void
    {
        $personId      = $this->createCustomerWithPoints(100);
        $customerModel = model(Customer::class);

        $result = $customerModel->adjustRewardPoints($personId, -40);

        $this->assertTrue($result);
        $this->assertSame(60, $this->getCustomerPoints($personId));
    }

    public function testAdjustRewardPointsDecrementFailsWithInsufficientPoints(): void
    {
        $personId      = $this->createCustomerWithPoints(20);
        $customerModel = model(Customer::class);

        $result = $customerModel->adjustRewardPoints($personId, -40);

        $this->assertFalse($result);
        $this->assertSame(20, $this->getCustomerPoints($personId));
    }

    public function testAdjustRewardPointsDecrementExactBalanceSucceeds(): void
    {
        $personId      = $this->createCustomerWithPoints(40);
        $customerModel = model(Customer::class);

        $result = $customerModel->adjustRewardPoints($personId, -40);

        $this->assertTrue($result);
        $this->assertSame(0, $this->getCustomerPoints($personId));
    }

    public function testAdjustRewardPointsConcurrentDecrementsOneWins(): void
    {
        $personId = $this->createCustomerWithPoints(100);
        $db       = \Config\Database::connect();
        $table    = $db->DBPrefix . 'customers';

        $sql = sprintf(
            'UPDATE %s SET points = points - 60 WHERE person_id = %s AND points >= 60',
            $table,
            $db->escape($personId)
        );

        [$affectedRows1, $affectedRows2] = $this->raceTwoUpdates($sql, $sql);

        $this->assertSame(1, $affectedRows1 + $affectedRows2);
        $this->assertSame(40, $this->getCustomerPoints($personId));
    }
}
