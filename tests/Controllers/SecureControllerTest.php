<?php

namespace Tests\Controllers;

use App\Controllers\Secure_Controller;
use CodeIgniter\Test\CIUnitTestCase;

class SecureControllerTest extends CIUnitTestCase
{
    public function testTablePaginationUsesSafeDefaultsForInvalidValues(): void
    {
        $this->assertSame([25, 0], TestableSecureController::sanitizePagination('0', '-1'));
        $this->assertSame([25, 0], TestableSecureController::sanitizePagination('not-a-number', []));
    }

    public function testTablePaginationCapsPageSizeAndPreservesValidOffset(): void
    {
        $this->assertSame([100, 50], TestableSecureController::sanitizePagination('1000', '50'));
        $this->assertSame([50, 0], TestableSecureController::sanitizePagination(50, 0));
    }
}

class TestableSecureController extends Secure_Controller
{
    public static function sanitizePagination(mixed $limit, mixed $offset): array
    {
        return self::sanitizeTablePagination($limit, $offset);
    }
}
