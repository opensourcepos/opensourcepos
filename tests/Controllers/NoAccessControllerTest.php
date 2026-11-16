<?php

namespace Tests\Controllers;

use App\Controllers\No_access;
use App\Models\Module;
use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseBootstrapTrait;

class NoAccessControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $refresh = false;
    protected $namespace = null;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        Factories::reset('models');

        parent::tearDown();
    }

    public function testGetIndex_EscapesHostileMarkupInModuleNameAndPermissionId(): void
    {
        $moduleMock = $this->createMock(Module::class);
        $moduleMock->method('get_module_name')
            ->willReturn('<img src=x onerror=alert(1)>');
        Factories::injectMock('models', Module::class, $moduleMock);

        Services::session()->destroy();

        $controller = new No_access();
        $controller->initController(Services::request(), Services::response(), Services::logger());

        $rendered = $controller->getIndex('sales', '<script>alert("perm")</script>');

        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $rendered);
        $this->assertStringContainsString('(&lt;script&gt;alert(&quot;perm&quot;)&lt;/script&gt;)', $rendered);
        $this->assertStringNotContainsString('<img src=x', $rendered);
        $this->assertStringNotContainsString('<script>alert("perm")</script>', $rendered);

        $this->withSession(['person_id' => 1, 'menu_group' => 'home']);
        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get('/no_access/sales/sales_delete');

        $response->assertStatus(200);
        $this->assertStringContainsString('(sales_delete)', (string) $response->response()->getBody());
    }

    public function testGetIndex_OmitsParenthesesWhenPermissionIdEmpty(): void
    {
        $rendered = view('no_access', [
            'module_name'   => '<b>Sales</b>',
            'permission_id' => '',
        ]);

        $this->assertStringContainsString('&lt;b&gt;Sales&lt;/b&gt;', $rendered);
        $this->assertStringNotContainsString('<b>Sales</b>', $rendered);
        $this->assertStringNotContainsString('()', $rendered);
    }
}
