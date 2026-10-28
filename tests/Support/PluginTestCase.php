<?php

namespace Tests\Support;

use App\Libraries\Plugins\PluginManager;
use CodeIgniter\Test\CIUnitTestCase;

abstract class PluginTestCase extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        PluginManager::registerAllNamespaces();
    }
}
