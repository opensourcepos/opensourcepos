<?php

namespace Tests\Jobs\Support;

use App\Jobs\Support\ItemCsvRowProcessor;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use RuntimeException;

class ItemCsvRowProcessorTest extends CIUnitTestCase
{
    public function testSaveTaxData_RejectsNegativePercent(): void
    {
        $processor = (new \ReflectionClass(ItemCsvRowProcessor::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($processor, 'saveTaxData');
        $method->setAccessible(true);

        $row = [
            'Tax 1 Name'    => 'VAT',
            'Tax 1 Percent' => '-5',
            'Tax 2 Name'    => '',
            'Tax 2 Percent' => '',
        ];

        $this->assertFalse($method->invoke($processor, $row, ['item_id' => 1]));
    }

    public function testSaveTaxData_RejectsNegativePercentOnTax2(): void
    {
        $processor = (new \ReflectionClass(ItemCsvRowProcessor::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($processor, 'saveTaxData');
        $method->setAccessible(true);

        $row = [
            'Tax 1 Name'    => '',
            'Tax 1 Percent' => '',
            'Tax 2 Name'    => 'GST',
            'Tax 2 Percent' => '-10',
        ];

        $this->assertFalse($method->invoke($processor, $row, ['item_id' => 1]));
    }

    private function invokeRunInTransaction(BaseConnection $db, callable $writeRow): bool
    {
        $processor = (new \ReflectionClass(ItemCsvRowProcessor::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($processor, 'runInTransaction');
        $method->setAccessible(true);

        return $method->invoke($processor, $db, $writeRow);
    }

    public function testRunInTransaction_CommitsSuccessfulRow(): void
    {
        $db = $this->createMock(BaseConnection::class);
        $db->method('transStatus')->willReturn(true);
        $db->expects($this->once())->method('transCommit')->willReturn(true);
        $db->expects($this->never())->method('transRollback');

        $this->assertTrue($this->invokeRunInTransaction($db, static fn (): bool => true));
    }

    public function testRunInTransaction_RollsBackRejectedRow(): void
    {
        $db = $this->createMock(BaseConnection::class);
        $db->method('transStatus')->willReturn(true);
        $db->expects($this->never())->method('transCommit');
        $db->expects($this->once())->method('transRollback');

        $this->assertFalse($this->invokeRunInTransaction($db, static fn (): bool => false));
    }

    public function testRunInTransaction_RollsBackAndRejectsWhenTransactionStatusFailed(): void
    {
        $db = $this->createMock(BaseConnection::class);
        $db->method('transStatus')->willReturn(false);
        $db->expects($this->never())->method('transCommit');
        $db->expects($this->once())->method('transRollback');

        $this->expectException(RuntimeException::class);
        $this->invokeRunInTransaction($db, static fn (): bool => true);
    }

    public function testRunInTransaction_RejectsFailedCommit(): void
    {
        $db = $this->createMock(BaseConnection::class);
        $db->method('transStatus')->willReturn(true);
        $db->method('transCommit')->willReturn(false);
        $db->expects($this->once())->method('transRollback');

        $this->expectException(RuntimeException::class);
        $this->invokeRunInTransaction($db, static fn (): bool => true);
    }

    public function testRunInTransaction_RollsBackAndRethrowsOnException(): void
    {
        $db = $this->createMock(BaseConnection::class);
        $db->expects($this->once())->method('transRollback');

        $this->expectException(RuntimeException::class);
        $this->invokeRunInTransaction($db, static function (): bool {
            throw new RuntimeException('write failed');
        });
    }
}
