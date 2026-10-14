<?php

namespace Tests\Libraries;

use CodeIgniter\Test\CIUnitTestCase;
use App\Enums\RewardOperation;
use App\Libraries\Reward_lib;
use App\Models\Customer;

class Reward_libTest extends CIUnitTestCase
{
    use \CodeIgniter\Test\DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    private Reward_lib $rewardLib;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rewardLib = new Reward_lib();
    }

    /**
     * Test calculatePointsEarned returns correct calculation
     */
    public function testCalculatePointsEarnedReturnsCorrectValue(): void
    {
        $pointsEarned = $this->rewardLib->calculatePointsEarned(100.00, 10);
        $this->assertEquals(10.0, $pointsEarned);
    }

    /**
     * Test calculatePointsEarned with zero amount
     */
    public function testCalculatePointsEarnedWithZeroAmount(): void
    {
        $pointsEarned = $this->rewardLib->calculatePointsEarned(0, 10);
        $this->assertEquals(0.0, $pointsEarned);
    }

    /**
     * Test calculatePointsEarned with zero percentage
     */
    public function testCalculatePointsEarnedWithZeroPercentage(): void
    {
        $pointsEarned = $this->rewardLib->calculatePointsEarned(100.00, 0);
        $this->assertEquals(0.0, $pointsEarned);
    }

    /**
     * Test calculatePointsEarned with percentage over 100
     */
    public function testCalculatePointsEarnedWithHighPercentage(): void
    {
        $pointsEarned = $this->rewardLib->calculatePointsEarned(50.00, 200);
        $this->assertEquals(100.0, $pointsEarned);
    }

    /**
     * Test hasSufficientPoints returns true when customer has enough points
     */
    public function testHasSufficientPointsReturnsTrueWhenSufficient(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 100]);

        // Use reflection to inject mock
        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $this->assertTrue($this->rewardLib->hasSufficientPoints(1, 50));
    }

    /**
     * Test hasSufficientPoints returns false when customer has insufficient points
     */
    public function testHasSufficientPointsReturnsFalseWhenInsufficient(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 30]);

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $this->assertFalse($this->rewardLib->hasSufficientPoints(1, 50));
    }

    /**
     * Test getPointsBalance returns correct balance
     */
    public function testGetPointsBalanceReturnsCorrectValue(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 250]);

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $this->assertEquals(250, $this->rewardLib->getPointsBalance(1));
    }

    /**
     * Test calculateRewardPaymentAmount with mixed payments
     */
    public function testCalculateRewardPaymentAmountWithMixedPayments(): void
    {
        $payments = [
            ['payment_type' => 'Cash', 'payment_amount' => 50],
            ['payment_type' => 'Rewards', 'payment_amount' => 25],
            ['payment_type' => 'Credit Card', 'payment_amount' => 100],
            ['payment_type' => 'Rewards', 'payment_amount' => 15],
        ];

        $rewardLabels = ['Rewards', 'Points'];

        $total = $this->rewardLib->calculateRewardPaymentAmount($payments, $rewardLabels);

        $this->assertEquals(40.0, $total);
    }

    /**
     * Test calculateRewardPaymentAmount with empty payments
     */
    public function testCalculateRewardPaymentAmountWithEmptyPayments(): void
    {
        $total = $this->rewardLib->calculateRewardPaymentAmount([], ['Rewards']);
        $this->assertEquals(0.0, $total);
    }

    /**
     * Test calculateRewardPaymentAmount with no matching labels
     */
    public function testCalculateRewardPaymentAmountWithNoMatchingLabels(): void
    {
        $payments = [
            ['payment_type' => 'Cash', 'payment_amount' => 50],
            ['payment_type' => 'Credit Card', 'payment_amount' => 100],
        ];

        $total = $this->rewardLib->calculateRewardPaymentAmount($payments, ['Rewards']);
        $this->assertEquals(0.0, $total);
    }

    /**
     * Test adjustRewardPoints returns false for null customer
     */
    public function testAdjustRewardPointsReturnsFalseForNullCustomer(): void
    {
        $result = $this->rewardLib->adjustRewardPoints(null, 50, RewardOperation::Deduct);
        $this->assertFalse($result);
    }

    /**
     * Test adjustRewardPoints returns false for zero amount
     */
    public function testAdjustRewardPointsReturnsFalseForZeroAmount(): void
    {
        $result = $this->rewardLib->adjustRewardPoints(1, 0, RewardOperation::Deduct);
        $this->assertFalse($result);
    }

    /**
     * Test adjustRewardDelta returns false for null customer
     */
    public function testAdjustRewardDeltaReturnsFalseForNullCustomer(): void
    {
        $result = $this->rewardLib->adjustRewardDelta(null, 50);
        $this->assertFalse($result);
    }

    /**
     * Test adjustRewardDelta returns false for zero adjustment
     */
    public function testAdjustRewardDeltaReturnsFalseForZeroAdjustment(): void
    {
        $result = $this->rewardLib->adjustRewardDelta(1, 0);
        $this->assertFalse($result);
    }

    /**
     * Test handleCustomerChange with same customer returns empty result
     */
    public function testHandleCustomerChangeWithSameCustomerReturnsEmpty(): void
    {
        $result = $this->rewardLib->handleCustomerChange(1, 1, 50.0, 75.0);
        $this->assertEquals(['restored' => 0.0, 'charged' => 0.0, 'insufficient' => false], $result);
    }

    /**
     * Test handleCustomerChange with null customers
     */
    public function testHandleCustomerChangeWithNullCustomers(): void
    {
        $result = $this->rewardLib->handleCustomerChange(null, null, 50.0, 75.0);
        $this->assertEquals(['restored' => 0.0, 'charged' => 0.0, 'insufficient' => false], $result);
    }

    /**
     * Test handleCustomerChange when customer changes from null to valid customer
     */
    public function testHandleCustomerChangeFromNullToValidCustomer(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info', 'update_reward_points_value'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 100]);

        $customerModel->method('update_reward_points_value')
            ->willReturn(true);

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $result = $this->rewardLib->handleCustomerChange(null, 2, 0, 50.0);

        $this->assertEquals(50.0, $result['charged']);
        $this->assertEquals(0.0, $result['restored']);
    }

    /**
     * Test update reward points correctly deducts from balance
     */
    public function testPointsUpdateDuringSaleCreation(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info', 'update_reward_points_value'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 200]);

        $customerModel->expects($this->once())
            ->method('update_reward_points_value')
            ->with(1, 150);

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $this->rewardLib->adjustRewardPoints(1, 50, RewardOperation::Deduct);
    }

    /**
     * Test update reward points correctly restores on sale deletion
     */
    public function testPointsRestoreOnSaleDeletion(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info', 'update_reward_points_value'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 150]);

        $customerModel->expects($this->once())
            ->method('update_reward_points_value')
            ->with(1, 200);

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $this->rewardLib->adjustRewardPoints(1, 50, RewardOperation::Restore);
    }

    /**
     * Test hasSufficientPoints returns true when points exactly match required
     */
    public function testHasSufficientPointsReturnsTrueWhenExactMatch(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 50]);

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $this->assertTrue($this->rewardLib->hasSufficientPoints(1, 50));
    }

    /**
     * Test adjustRewardPoints returns false when insufficient points for deduct
     */
    public function testAdjustRewardPointsReturnsFalseWhenInsufficientPointsForDeduct(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info', 'update_reward_points_value'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 30]);

        $customerModel->expects($this->never())
            ->method('update_reward_points_value');

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $result = $this->rewardLib->adjustRewardPoints(1, 50, RewardOperation::Deduct);
        $this->assertFalse($result);
    }

    /**
     * Test adjustRewardDelta returns false when insufficient points for positive adjustment
     */
    public function testAdjustRewardDeltaReturnsFalseWhenInsufficientPoints(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info', 'update_reward_points_value'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 20]);

        $customerModel->expects($this->never())
            ->method('update_reward_points_value');

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $result = $this->rewardLib->adjustRewardDelta(1, 50);
        $this->assertFalse($result);
    }

    /**
     * Test adjustRewardDelta succeeds for negative adjustment (refund)
     */
    public function testAdjustRewardDeltaSucceedsForNegativeAdjustment(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info', 'update_reward_points_value'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 100]);

        $customerModel->expects($this->once())
            ->method('update_reward_points_value')
            ->with(1, 150);

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $result = $this->rewardLib->adjustRewardDelta(1, -50);
        $this->assertTrue($result);
    }

    /**
     * Test handleCustomerChange caps charge at available points when insufficient
     */
    public function testHandleCustomerChangeCapsChargeWhenInsufficient(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info', 'update_reward_points_value'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 30]);

        $customerModel->expects($this->once())
            ->method('update_reward_points_value')
            ->with(2, 0);

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $result = $this->rewardLib->handleCustomerChange(null, 2, 0, 50.0);

        $this->assertEquals(30.0, $result['charged']);
        $this->assertTrue($result['insufficient']);
    }

    /**
     * Test handleCustomerChange does not charge when new customer has zero points
     */
    public function testHandleCustomerChangeCapsChargeAtZero(): void
    {
        $customerModel = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['get_info', 'update_reward_points_value'])
            ->getMock();

        $customerModel->method('get_info')
            ->willReturn((object)['points' => 0]);

        $customerModel->expects($this->once())
            ->method('update_reward_points_value')
            ->with(2, 0);

        $reflection = new \ReflectionClass($this->rewardLib);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);
        $property->setValue($this->rewardLib, $customerModel);

        $result = $this->rewardLib->handleCustomerChange(null, 2, 0, 50.0);

        $this->assertEquals(0.0, $result['charged']);
        $this->assertTrue($result['insufficient']);
    }
}