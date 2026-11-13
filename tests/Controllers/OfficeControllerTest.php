<?php

namespace Tests\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseBootstrapTrait;

class OfficeControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use DatabaseBootstrapTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $seedOnce    = true;
    protected $refresh     = false;
    protected $namespace   = null;

    protected function setUp(): void
    {
        $this->resetDatabaseOnce();

        parent::setUp();
    }

    public function testPostLogout_RedirectsToLogin(): void
    {
        $this->withSession([
            'person_id'  => 1,
            'menu_group' => 'office',
        ]);

        $response = $this->post('/office/logout');

        $response->assertRedirectTo('login');
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
