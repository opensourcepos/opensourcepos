<?php

namespace Tests\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\OSPOS;
use ReflectionMethod;

class OSPOSTest extends CIUnitTestCase
{
    public function testGetDefaultSettings_ProvidesSaleTypeOptionKeys(): void
    {
        $config = new class extends OSPOS {
            public function __construct() {}
        };

        $method = new ReflectionMethod(OSPOS::class, 'getDefaultSettings');
        $method->setAccessible(true);
        $settings = $method->invoke($config);

        $this->assertArrayHasKey('invoice_enable', $settings);
        $this->assertArrayHasKey('work_order_enable', $settings);
    }
}
