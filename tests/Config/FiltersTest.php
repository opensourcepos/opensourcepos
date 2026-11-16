<?php

namespace Tests\Config;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Filters;
use ReflectionClass;
use Tests\Support\DatabaseBootstrapTrait;

class FiltersTest extends CIUnitTestCase
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
        Factories::reset('config');
        Services::resetSingle('filters');
        Services::resetSingle('security');
        Services::resetSingle('throttler');

        parent::tearDown();
    }

    private function enableCsrfFilter(): void
    {
        $defaultProperties = (new ReflectionClass(Filters::class))->getDefaultProperties();
        $filtersConfig = config(Filters::class);
        $filtersConfig->globals['before']['csrf'] = $defaultProperties['globals']['before']['csrf'];
        Services::resetSingle('filters');
        Services::resetSingle('security');
    }

    public function testGlobalsBefore_ConfiguresLoginAndMigrateExclusionsAsArrays(): void
    {
        $defaultProperties = (new ReflectionClass(Filters::class))->getDefaultProperties();
        $before = $defaultProperties['globals']['before'];

        $this->assertSame(['login', 'migrate'], $before['isLoggedIn']['except']);
        $this->assertSame(['login', 'migrate'], $before['csrf']['except']);
    }

    public function testBeforeFilters_AllowsLoginAndMigrateWithoutAuthOrCsrfToken(): void
    {
        $this->enableCsrfFilter();
        Services::session()->destroy();

        $loginGetResponse = $this->get('/login');
        $loginGetResponse->assertStatus(200);

        $loginPostResponse = $this->post('/login', [
            'username' => '',
            'password' => '',
        ]);
        $this->assertNotSame(403, $loginPostResponse->response()->getStatusCode());

        $migratePostResponse = $this->post('/migrate', [
            'username' => '',
            'password' => '',
        ]);
        $migratePostResponse->assertStatus(401);
    }

    public function testBeforeFilters_RequiresAuthenticationOnProtectedRoute(): void
    {
        $this->enableCsrfFilter();
        Services::session()->destroy();

        $response = $this->get('/home');

        $response->assertRedirectTo('login');
    }

    public function testBeforeFilters_RejectsTokenlessPostOnProtectedRouteWhenCsrfEnabled(): void
    {
        $this->enableCsrfFilter();
        $this->withSession(['person_id' => 1, 'menu_group' => 'home']);

        $this->expectException(SecurityException::class);
        $this->post('/home/save', [
            'current_password' => 'pointofsale',
            'password'         => 'newpassword123',
        ]);
    }
}
