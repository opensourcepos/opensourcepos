<?php

namespace Tests\Models;

use App\Models\Giftcard;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Tests\Support\ConcurrentDbRaceTrait;

/**
 * Regression tests for GHSA-995p-52qw-5hh2: decrementGiftcardValue() must
 * apply its balance check and its write in a single atomic UPDATE, so that
 * two concurrent decrements against the same gift card can never both read
 * the same stale balance and double-spend it.
 */
class GiftcardTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use ConcurrentDbRaceTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    public static function setUpBeforeClass(): void
    {
        $seeder = Database::seeder('tests');
        $seeder->call('TestDatabaseBootstrapSeeder');
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createGiftcard(float $value): int
    {
        $giftcardNumber = random_int(1000000, 9999999);

        \Config\Database::connect()->table('giftcards')->insert([
            'giftcard_number' => $giftcardNumber,
            'value'           => $value,
            'deleted'         => 0,
            'person_id'       => null,
        ]);

        return $giftcardNumber;
    }

    protected function getGiftcardValue(int $giftcardNumber): float
    {
        $row = \Config\Database::connect()->table('giftcards')
            ->where('giftcard_number', $giftcardNumber)
            ->get()
            ->getRow();

        return (float) $row->value;
    }

    public function testDecrementGiftcardValueSucceedsWithSufficientBalance(): void
    {
        $giftcardNumber = $this->createGiftcard(100.00);
        $giftcardModel  = model(Giftcard::class);

        $result = $giftcardModel->decrementGiftcardValue((string) $giftcardNumber, 60.00);

        $this->assertTrue($result);
        $this->assertEqualsWithDelta(40.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testDecrementGiftcardValueFailsWithInsufficientBalance(): void
    {
        $giftcardNumber = $this->createGiftcard(50.00);
        $giftcardModel  = model(Giftcard::class);

        $result = $giftcardModel->decrementGiftcardValue((string) $giftcardNumber, 60.00);

        $this->assertFalse($result);
        $this->assertEqualsWithDelta(50.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testDecrementGiftcardValueRejectsNegativeAmount(): void
    {
        $giftcardNumber = $this->createGiftcard(100.00);
        $giftcardModel  = model(Giftcard::class);

        $result = $giftcardModel->decrementGiftcardValue((string) $giftcardNumber, -500.00);

        $this->assertFalse($result);
        $this->assertEqualsWithDelta(100.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testDecrementGiftcardValueRejectsZeroAmount(): void
    {
        $giftcardNumber = $this->createGiftcard(100.00);
        $giftcardModel  = model(Giftcard::class);

        $result = $giftcardModel->decrementGiftcardValue((string) $giftcardNumber, 0.00);

        $this->assertFalse($result);
        $this->assertEqualsWithDelta(100.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testDecrementGiftcardValueExactBalanceSucceeds(): void
    {
        $giftcardNumber = $this->createGiftcard(60.00);
        $giftcardModel  = model(Giftcard::class);

        $result = $giftcardModel->decrementGiftcardValue((string) $giftcardNumber, 60.00);

        $this->assertTrue($result);
        $this->assertEqualsWithDelta(0.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testDecrementGiftcardValueFailsWhenDeleted(): void
    {
        $giftcardNumber = $this->createGiftcard(100.00);

        \Config\Database::connect()->table('giftcards')
            ->where('giftcard_number', $giftcardNumber)
            ->update(['deleted' => 1]);

        $giftcardModel = model(Giftcard::class);

        $result = $giftcardModel->decrementGiftcardValue((string) $giftcardNumber, 60.00);

        $this->assertFalse($result);
        $this->assertEqualsWithDelta(100.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testDecrementGiftcardValueConcurrentDecrementsOneWins(): void
    {
        $giftcardNumber = $this->createGiftcard(100.00);

        [$result1, $result2] = $this->raceTwoProcesses(
            'giftcard',
            [$giftcardNumber, 60.00],
            [$giftcardNumber, 60.00]
        );

        $this->assertSame(1, (int) $result1 + (int) $result2);
        $this->assertEqualsWithDelta(40.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }
}
