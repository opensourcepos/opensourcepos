<?php

namespace Tests\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\OsposConfigFixtureTrait;

class LocaleHelperTest extends CIUnitTestCase
{
    use OsposConfigFixtureTrait;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../app/Helpers/locale_helper.php';

        $this->injectOsposSettings(['dateformat' => 'Y-m-d']);
    }

    public function testValidDateReturnsTrue(): void
    {
        $this->assertTrue(isValidDate('2024-06-10'));
    }

    public function testInvalidDateFormatReturnsFalse(): void
    {
        $this->assertFalse(isValidDate('10/06/2024'));
    }

    public function testImpossibleDateReturnsFalse(): void
    {
        $this->assertFalse(isValidDate('2024-13-01'));
    }

    public function testPhpDateOverflowReturnsFalse(): void
    {
        // PHP silently overflows Feb 30 → Mar 1; the format()===candidate check catches this
        $this->assertFalse(isValidDate('2024-02-30'));
    }

    public function testEmptyStringReturnsFalse(): void
    {
        $this->assertFalse(isValidDate(''));
    }

    public function testLeapDayValidReturnsTrue(): void
    {
        $this->assertTrue(isValidDate('2024-02-29'));
    }

    public function testLeapDayInvalidYearReturnsFalse(): void
    {
        $this->assertFalse(isValidDate('2023-02-29'));
    }

    public function testPartialDateReturnsFalse(): void
    {
        $this->assertFalse(isValidDate('2024-06'));
    }
}
