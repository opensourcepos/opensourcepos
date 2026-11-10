<?php

namespace Tests\Support;

use App\Libraries\ImportBatchService;
use Config\Services;
use RuntimeException;

/**
 * Injects a partial ImportBatchService stub that runs the real service for the
 * first N-1 pushRow() calls (so at least one queue_jobs row is written) then
 * either returns false or throws on call N, letting the caller verify that the
 * surrounding transaction is rolled back cleanly.
 *
 * After the request completes, $this->importBatchPushRowCalls holds the total
 * number of pushRow() invocations so tests can assert the failure path was reached.
 *
 * Call resetImportBatchMock() after the request to clear the injected mock.
 */
trait ImportBatchFailureTrait
{
    private int $importBatchPushRowCalls = 0;

    private function injectPushRowFalseOnCall(int $failOnCall): void
    {
        $this->importBatchPushRowCalls = 0;
        $real                          = new ImportBatchService();
        $stub                          = $this->createMock(ImportBatchService::class);

        $stub->method('create')->willReturnCallback(fn (...$args) => $real->create(...$args));
        $stub->method('pushRow')->willReturnCallback(function (...$args) use ($real, $failOnCall) {
            $this->importBatchPushRowCalls++;

            if ($this->importBatchPushRowCalls < $failOnCall) {
                return $real->pushRow(...$args);
            }

            return false;
        });

        Services::injectMock('importBatch', $stub);
    }

    private function injectPushRowThrowOnCall(int $throwOnCall): void
    {
        $this->importBatchPushRowCalls = 0;
        $real                          = new ImportBatchService();
        $stub                          = $this->createMock(ImportBatchService::class);

        $stub->method('create')->willReturnCallback(fn (...$args) => $real->create(...$args));
        $stub->method('pushRow')->willReturnCallback(function (...$args) use ($real, $throwOnCall) {
            $this->importBatchPushRowCalls++;

            if ($this->importBatchPushRowCalls < $throwOnCall) {
                return $real->pushRow(...$args);
            }

            throw new RuntimeException('Simulated queue failure');
        });

        Services::injectMock('importBatch', $stub);
    }

    private function resetImportBatchMock(): void
    {
        Services::injectMock('importBatch', null);
    }
}
