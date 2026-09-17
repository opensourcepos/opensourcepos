<?php

namespace Tests\Models;

use App\Models\JobThrottle;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class JobThrottleModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
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

    public function testExistsReturnsFalseForUnknownId(): void
    {
        $this->assertFalse($this->jobThrottle->exists(999999));
    }

    public function testSaveValueInsertsWhenThrottleDoesNotExist(): void
    {
        $this->assertTrue($this->jobThrottle->saveValue(['max_count' => 10, 'period' => 'hour'], 1));

        $this->seeInDatabase('job_throttles', [
            'throttle_id' => 1,
            'max_count'   => 10,
            'period'      => 'hour',
            'deleted'     => 0,
        ]);
    }

    public function testSaveValueUpdatesWhenThrottleExists(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 10, 'period' => 'hour'], 1);
        $this->assertTrue($this->jobThrottle->exists(1));

        $this->assertTrue($this->jobThrottle->saveValue(['max_count' => 25, 'period' => 'day'], 1));

        $this->seeInDatabase('job_throttles', [
            'throttle_id' => 1,
            'max_count'   => 25,
            'period'      => 'day',
        ]);

        $this->assertEquals(1, $this->db->table('job_throttles')->countAllResults());
    }

    public function testGetAllExcludesDeletedThrottles(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 5, 'period' => 'minute'], 1);
        $this->jobThrottle->saveValue(['max_count' => 15, 'period' => 'day'], 2);
        $this->jobThrottle->delete(2);

        $results = $this->jobThrottle->getAll()->getResultArray();

        $this->assertCount(1, $results);
        $this->assertSame('1', (string) $results[0]['throttle_id']);
    }

    public function testDeleteSoftDeletesThrottle(): void
    {
        $this->jobThrottle->saveValue(['max_count' => 5, 'period' => 'minute'], 1);

        $this->assertTrue($this->jobThrottle->delete(1));

        $this->seeInDatabase('job_throttles', ['throttle_id' => 1, 'deleted' => 1]);
        $this->assertCount(0, $this->jobThrottle->getAll()->getResultArray());
    }
}
