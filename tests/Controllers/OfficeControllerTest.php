<?php

namespace Tests\Controllers;

use App\Models\Employee;
use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Session as SessionConfig;
use Tests\Support\DatabaseBootstrapTrait;

class OfficeControllerTest extends CIUnitTestCase
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
        Services::resetSingle('session');

        parent::tearDown();
    }

    public function testPostLogout_DestroysSessionAndRedirectsToLogin(): void
    {
        $sessionConfig = config(SessionConfig::class);
        $handler = new ArrayHandler($sessionConfig, '127.0.0.1');
        $sessionSpy = new class ($handler, $sessionConfig) extends Session {
            public bool $destroyed = false;

            public function destroy(): void
            {
                $this->destroyed = true;
                $_SESSION = [];
            }
        };
        $sessionSpy->setLogger(Services::logger());
        Services::injectMock('session', $sessionSpy);

        $this->withSession([
            'person_id'  => 1,
            'menu_group' => 'office',
        ]);

        $response = $this->post('/office/logout');

        $response->assertRedirectTo('login');
        $this->assertTrue($sessionSpy->destroyed);
        $this->assertNull(session('person_id'));
        $this->assertFalse(model(Employee::class)->is_logged_in());
    }

    public function testPostLogout_RejectsGetRequest(): void
    {
        $this->withSession([
            'person_id'  => 1,
            'menu_group' => 'office',
        ]);

        $this->expectException(PageNotFoundException::class);
        $this->get('/office/logout');
    }
}
