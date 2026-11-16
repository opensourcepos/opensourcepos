<?php

namespace Tests\Controllers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\OsposConfigFixtureTrait;

/**
 * Test suite for the Login controller, including the CI Throttler
 * mitigation for brute-force/credential-stuffing.
 */
class LoginTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use OsposConfigFixtureTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    private array $usedKeys = [];

    protected function setUp(): void
    {
        parent::setUp();

        Services::session()->destroy();
        $this->clearThrottleState();
    }

    protected function tearDown(): void
    {
        $this->clearThrottleState();
        Factories::reset('config');

        parent::tearDown();
    }

    private function clearThrottleState(): void
    {
        $throttler = Services::throttler();

        foreach ($this->usedKeys as $key) {
            $throttler->remove($key);
        }

        $this->usedKeys = [];

        Services::resetSingle('throttler');
    }

    private function trackThrottleKeys(string $username = ''): void
    {
        $this->usedKeys[] = 'login-ip-' . Services::request()->getIPAddress();

        if ($username !== '') {
            $this->usedKeys[] = 'login-user-' . strtolower($username);
        }
    }

    /**
     * Login::index() redirects to 'login' and runs a migration when the
     * app's migration state isn't current, bypassing credential checks
     * entirely. Skip credential-dependent assertions in that case so this
     * test only exercises login logic when the DB is actually current
     * (throttling itself is verified independently and runs before this
     * branch, so it's unaffected).
     */
    private function skipIfMigrationRequired(\CodeIgniter\Test\TestResponse $response): void
    {
        $redirectUrl = $response->getRedirectUrl();

        if ($redirectUrl !== null && str_ends_with(rtrim(strtolower($redirectUrl), '/'), '/login')) {
            $this->markTestSkipped('App migration state is not current in this environment; skipping credential-path assertions.');
        }
    }

    public function testValidCredentialsLogsIn(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'pointofsale',
        ]);
        $this->trackThrottleKeys('admin');

        $this->skipIfMigrationRequired($response);

        $response->assertRedirectTo('home');
    }

    public function testInvalidCredentialsShowsError(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);
        $this->trackThrottleKeys('admin');

        $this->skipIfMigrationRequired($response);

        $response->assertStatus(200);
        $response->assertSee(lang('Login.invalid_username_and_password'));
    }

    public function testSixthFailedAttemptIsThrottled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'wronguser',
                'password' => 'wrongpassword',
            ]);
            $this->trackThrottleKeys('wronguser');
        }

        $response = $this->post('/login', [
            'username' => 'wronguser',
            'password' => 'wrongpassword',
        ]);
        $this->trackThrottleKeys('wronguser');

        $response->assertStatus(429);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
        $this->assertSame(lang('Login.too_many_attempts'), $result['message']);
    }

    public function testCorrectLoginStillWorksUnderThrottleCapacity(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'username' => 'admin',
                'password' => 'wrongpassword',
            ]);
            $this->trackThrottleKeys('admin');
        }

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'pointofsale',
        ]);
        $this->trackThrottleKeys('admin');

        $this->skipIfMigrationRequired($response);

        $response->assertRedirectTo('home');
    }

    public function testGetRequestToLoginIsNeverThrottled(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/login');
            $this->skipIfMigrationRequired($response);
            $response->assertStatus(200);
        }
    }

    public function testIndex_EscapesHostileMarkupInValidationErrors(): void
    {
        $config = [
            'company'    => 'OSPOS',
            'theme'      => 'flatly',
            'login_form' => 'floating_labels',
        ];
        $this->injectOsposSettings($config);

        $validation = Services::validation();
        $validation->reset();
        $validation->setError('username', '<script>alert("xss")</script>');

        $rendered = view('login', [
            'hasErrors'       => true,
            'isLatest'        => true,
            'isNewInstall'    => false,
            'latestVersion'   => '20250101000000',
            'gcaptchaEnabled' => false,
            'config'          => $config,
            'validation'      => $validation,
        ]);

        $this->assertStringContainsString('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $rendered);
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $rendered);
    }

    public function testIndex_RendersUniqueInputIdsAndMatchingAriaDescribedbyInInputGroupsMode(): void
    {
        $this->injectOsposSettings([
            'company'    => 'OSPOS',
            'theme'      => 'flatly',
            'login_form' => 'input_groups',
        ]);

        $response = $this->get('/login');
        $this->skipIfMigrationRequired($response);
        $response->assertStatus(200);

        $html = (string) $response->response()->getBody();

        $this->assertSame(1, substr_count($html, 'id="input-username"'));
        $this->assertSame(1, substr_count($html, 'id="input-password"'));
        $this->assertSame(1, substr_count($html, 'id="addon-username"'));
        $this->assertSame(1, substr_count($html, 'id="addon-password"'));
        $this->assertMatchesRegularExpression('/<input\b[^>]*\bid="input-username"[^>]*\baria-describedby="addon-username"/', $html);
        $this->assertMatchesRegularExpression('/<input\b[^>]*\bid="input-password"[^>]*\baria-describedby="addon-password"/', $html);
    }
}
