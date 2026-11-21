<?php

namespace Tests\Models;

use App\Models\Giftcard;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Tests\Support\ConcurrentDbRaceTrait;
use Tests\Support\DatabaseBootstrapTrait;

/**
 * Regression tests: decrementGiftcardValue() must
 * apply its balance check and its write in a single atomic UPDATE, so that
 * two concurrent decrements against the same gift card can never both read
 * the same stale balance and double-spend it.
 */
class GiftcardTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use ConcurrentDbRaceTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();
    }

    protected function createGiftcard(float $value): int
    {
        $giftcardNumber = random_int(1000000, 9999999);

        Database::connect()->table('giftcards')->insert([
            'giftcard_number' => $giftcardNumber,
            'value'           => $value,
            'deleted'         => 0,
            'person_id'       => null,
        ]);

        return $giftcardNumber;
    }

    protected function getGiftcardValue(int $giftcardNumber): float
    {
        $row = Database::connect()->table('giftcards')
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

        Database::connect()->table('giftcards')
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

    public function testIncrementGiftcardValueSucceeds(): void
    {
        $giftcardNumber = $this->createGiftcard(50.00);
        $giftcardModel  = model(Giftcard::class);

        $result = $giftcardModel->incrementGiftcardValue((string) $giftcardNumber, 30.00);

        $this->assertTrue($result);
        $this->assertEqualsWithDelta(80.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testIncrementGiftcardValueFailsWhenDeleted(): void
    {
        $giftcardNumber = $this->createGiftcard(50.00);

        Database::connect()->table('giftcards')
            ->where('giftcard_number', $giftcardNumber)
            ->update(['deleted' => 1]);

        $giftcardModel = model(Giftcard::class);

        $result = $giftcardModel->incrementGiftcardValue((string) $giftcardNumber, 30.00);

        $this->assertFalse($result);
        $this->assertEqualsWithDelta(50.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testIncrementGiftcardValueRejectsNegativeAmount(): void
    {
        $giftcardNumber = $this->createGiftcard(50.00);
        $giftcardModel  = model(Giftcard::class);

        $result = $giftcardModel->incrementGiftcardValue((string) $giftcardNumber, -10.00);

        $this->assertFalse($result);
        $this->assertEqualsWithDelta(50.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testIncrementGiftcardValueRejectsZeroAmount(): void
    {
        $giftcardNumber = $this->createGiftcard(50.00);
        $giftcardModel  = model(Giftcard::class);

        $result = $giftcardModel->incrementGiftcardValue((string) $giftcardNumber, 0.00);

        $this->assertFalse($result);
        $this->assertEqualsWithDelta(50.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testSaveValue_PreservesGiftcardNumberOnInsert(): void
    {
        $giftcardModel = model(Giftcard::class);
        $customNumber = 'GC-CUSTOM-' . random_int(100000, 999999);
        $giftcardData = [
            'record_time'     => date('Y-m-d H:i:s'),
            'giftcard_number' => $customNumber,
            'value'           => 25.00,
            'person_id'       => null,
        ];

        $result = $giftcardModel->save_value($giftcardData, NEW_ENTRY);

        $this->assertTrue($result);
        $this->assertSame($customNumber, $giftcardData['giftcard_number']);
        $this->assertGreaterThan(0, (int) $giftcardData['giftcard_id']);
    }

    public function testGetGiftcardValue_ReturnsZeroForNonexistentGiftcardNumber(): void
    {
        $giftcardModel = model(Giftcard::class);

        $this->assertSame(0.0, $giftcardModel->get_giftcard_value('NONEXISTENT_GC_999999'));
    }

    public function testGetGiftcardCustomer_ReturnsZeroForNonexistentGiftcardNumber(): void
    {
        $giftcardModel = model(Giftcard::class);

        $this->assertSame(0, $giftcardModel->get_giftcard_customer('NONEXISTENT_GC_999999'));
    }
}
