<?php

namespace Tests\Controllers;

use App\Models\JobThrottle;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class JobsControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $seedOnce = true;
    protected $refresh = false;
    protected $namespace = null;

    private static bool $doneBootstrap = false;

    protected JobThrottle $jobThrottle;

    protected function setUp(): void
    {
        if (self::$doneBootstrap === false) {
            Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder');
            Config::connect($this->DBGroup)->close();

            self::$doneBootstrap = true;
        }

        parent::setUp();

        $this->jobThrottle = model(JobThrottle::class);
        $this->db->table('job_throttles')->truncate();
    }

    protected function loginAsAdmin(): void
    {
        $this->withSession([
            'person_id'  => 1,
            'menu_group' => 'office'
        ]);
    }

    public function testGetIndexRendersManageView(): void
    {
        $this->loginAsAdmin();

        $response = $this->get('/jobs');

        $response->assertStatus(200);
        $response->assertSee(lang('Jobs.settings'));
    }

    public function testPostSaveSettingsRejectsInvalidMode(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveSettings', [
            'mode'             => 'bogus',
            'web_max_seconds'  => 5,
            'task_max_seconds' => 30,
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostSaveSettingsRejectsNonNaturalMaxSeconds(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveSettings', [
            'mode'             => 'web',
            'web_max_seconds'  => -5,
            'task_max_seconds' => 30,
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostSaveSettingsSavesValidSettings(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveSettings', [
            'mode'                   => 'manual',
            'web_max_seconds'        => 10,
            'task_max_seconds'       => 20,
            'retry_limit'            => 5,
            'auto_purge'             => '1',
            'retention_days'         => 14,
            'failed_retention_days'  => 60,
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('app_config', ['key' => 'jobs_mode', 'value' => 'manual']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_web_max_seconds', 'value' => '10']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_task_max_seconds', 'value' => '20']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_retry_limit', 'value' => '5']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_auto_purge', 'value' => '1']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_retention_days', 'value' => '14']);
        $this->seeInDatabase('app_config', ['key' => 'jobs_failed_retention_days', 'value' => '60']);
    }

    public function testPostSaveThrottlesSavesAndDeletesMissingThrottles(): void
    {
        $this->loginAsAdmin();
        $this->jobThrottle->saveValue(['max_count' => 5, 'period' => 'minute'], 1);
        $this->jobThrottle->saveValue(['max_count' => 20, 'period' => 'day'], 2);

        // Throttle 2 is omitted from the post payload, so it should be soft-deleted.
        $response = $this->post('/jobs/saveThrottles', [
            'throttle_count_1'  => 15,
            'throttle_period_1' => 'hour',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('job_throttles', ['throttle_id' => 1, 'max_count' => 15, 'period' => 'hour', 'deleted' => 0]);
        $this->seeInDatabase('job_throttles', ['throttle_id' => 2, 'deleted' => 1]);
    }

    public function testPostSaveThrottlesAcceptsSecondPeriod(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveThrottles', [
            'throttle_count_1'  => 10,
            'throttle_period_1' => 'second',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);

        $this->seeInDatabase('job_throttles', ['throttle_id' => 1, 'max_count' => 10, 'period' => 'second', 'deleted' => 0]);
    }

    public function testPostSaveThrottlesRejectsInvalidPeriod(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/saveThrottles', [
            'throttle_count_1'  => 15,
            'throttle_period_1' => 'fortnight',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostSaveThrottlesLeavesExistingThrottlesUnchangedWhenPayloadInvalid(): void
    {
        $this->loginAsAdmin();
        $this->jobThrottle->saveValue(['max_count' => 5, 'period' => 'minute'], 1);
        $this->jobThrottle->saveValue(['max_count' => 20, 'period' => 'day'], 2);

        // Throttle 2 is invalid and throttle 1 is omitted; neither should be touched.
        $response = $this->post('/jobs/saveThrottles', [
            'throttle_count_2'  => 15,
            'throttle_period_2' => 'fortnight',
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);

        $this->seeInDatabase('job_throttles', ['throttle_id' => 1, 'max_count' => 5, 'period' => 'minute', 'deleted' => 0]);
        $this->seeInDatabase('job_throttles', ['throttle_id' => 2, 'max_count' => 20, 'period' => 'day', 'deleted' => 0]);
    }

    public function testGetThrottlesRendersPartial(): void
    {
        $this->loginAsAdmin();
        $this->jobThrottle->saveValue(['max_count' => 5, 'period' => 'minute'], 1);

        $response = $this->get('/jobs/throttles');

        $response->assertStatus(200);
        $response->assertSeeElement('#throttle_count_1');
    }

    public function testPostProcessJobsDrainsEmptyQueuesSuccessfully(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/processJobs', ['scope' => 'all']);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }

    public function testPostProcessJobsRejectsEmptySelection(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/processJobs', ['scope' => 'selected']);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertFalse($result['success']);
    }

    public function testPostProcessJobsIgnoresUnknownQueueNames(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/jobs/processJobs', [
            'scope'         => 'selected',
            'selected_jobs' => ['plugin.some_plugin', 'default'],
        ]);

        $response->assertStatus(200);
        $result = json_decode($response->getJSON(), true);
        $this->assertTrue($result['success']);
    }
}
