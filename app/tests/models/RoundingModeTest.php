<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;

use App\Models\Enums\Rounding_mode;

/**
 * @backupGlobals disabled
 */

class RoundingModeTest extends CIUnitTestCase
{

    public function setUp(): void
    {
        $this->resetInstance();
        $this->rounding_mode = model('enums/Rounding_mode');
    }

    public function test_rounding()
    {
        // $this->assertEquals(5.20, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.20, 2));
        $this->assertEquals(5.20, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.20, 2));
        $this->assertEquals(5.20, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.21, 2));
        $this->assertEquals(5.20, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.22, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.23, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.24, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.25, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.26, 2));
        $this->assertEquals(5.25, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.27, 2));
        $this->assertEquals(5.30, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.28, 2));
        $this->assertEquals(5.30, Rounding_mode::round_number(Rounding_mode::HALF_FIVE, 5.29, 2));
    }
}

