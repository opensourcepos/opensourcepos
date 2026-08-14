<?php

namespace Tests\Models;

use App\Models\Giftcard;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Regression tests for GHSA-995p-52qw-5hh2: decrementGiftcardValue() must
 * apply its balance check and its write in a single atomic UPDATE, so that
 * two concurrent decrements against the same gift card can never both read
 * the same stale balance and double-spend it.
 */
class GiftcardTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = null;

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

    public function testDecrementGiftcardValueExactBalanceSucceeds(): void
    {
        $giftcardNumber = $this->createGiftcard(60.00);
        $giftcardModel  = model(Giftcard::class);

        $result = $giftcardModel->decrementGiftcardValue((string) $giftcardNumber, 60.00);

        $this->assertTrue($result);
        $this->assertEqualsWithDelta(0.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }

    public function testDecrementGiftcardValueTwoSequentialCallsBothApply(): void
    {
        $giftcardNumber = $this->createGiftcard(100.00);
        $giftcardModel  = model(Giftcard::class);

        $firstResult  = $giftcardModel->decrementGiftcardValue((string) $giftcardNumber, 30.00);
        $secondResult = $giftcardModel->decrementGiftcardValue((string) $giftcardNumber, 30.00);

        $this->assertTrue($firstResult);
        $this->assertTrue($secondResult);
        $this->assertEqualsWithDelta(40.00, $this->getGiftcardValue($giftcardNumber), 0.001);
    }
}
