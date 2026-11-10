<?php

namespace Tests\Support;

use App\Libraries\ImportBatchService;
use CodeIgniter\Config\Services;
use RuntimeException;

/**
 * Injects a partial ImportBatchService stub that runs the real service for the
 * first N-1 pushRow() calls (so at least one queue_jobs row is written) then
 * either returns false or throws on call N, letting the caller verify that the
 * surrounding transaction is rolled back cleanly.
 *
 * Call resetImportBatchMock() in tearDown() — or rely on the auto-reset if
 * Services::reset() is already called there — to clear the injected mock.
 */
trait ImportBatchFailureTrait
{
    private function injectPushRowFalseOnCall(int $failOnCall): void
    {
        $callCount = 0;
        $real      = new ImportBatchService();
        $stub      = $this->createMock(ImportBatchService::class);

        $stub->method('create')->willReturnCallback(fn (...$args) => $real->create(...$args));
        $stub->method('pushRow')->willReturnCallback(function (...$args) use (&$callCount, $real, $failOnCall) {
            $callCount++;

            if ($callCount < $failOnCall) {
                return $real->pushRow(...$args);
            }

            return false;
        });

        Services::injectMock('importBatch', $stub);
    }

    private function injectPushRowThrowOnCall(int $throwOnCall): void
    {
        $callCount = 0;
        $real      = new ImportBatchService();
        $stub      = $this->createMock(ImportBatchService::class);

        $stub->method('create')->willReturnCallback(fn (...$args) => $real->create(...$args));
        $stub->method('pushRow')->willReturnCallback(function (...$args) use (&$callCount, $real, $throwOnCall) {
            $callCount++;

            if ($callCount < $throwOnCall) {
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
