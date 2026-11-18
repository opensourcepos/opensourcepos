<?php

namespace Tests\Jobs;

use App\Jobs\UnreleasedJobRegistry;
use CodeIgniter\Test\CIUnitTestCase;

class UnreleasedJobRegistryTest extends CIUnitTestCase
{
    private const WORKERS = 8;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ospos_unreleased_' . uniqid('', true) . '.json';
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    private function runInParallel(string $code, array $argumentSets): array
    {
        $processes = [];

        foreach ($argumentSets as $arguments) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, '-r', $code, $this->path, ...array_map('strval', $arguments)],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
                ROOTPATH
            );

            $this->assertIsResource($process);
            $processes[] = [$process, $pipes];
        }

        $outputs = [];

        foreach ($processes as [$process, $pipes]) {
            $outputs[] = trim((string)stream_get_contents($pipes[1]));
            $error = trim((string)stream_get_contents($pipes[2]));
            fclose($pipes[1]);
            fclose($pipes[2]);

            $this->assertSame(0, proc_close($process), $error);
        }

        return $outputs;
    }

    public function testRecord_ClaimReturnsEveryRecordedIdOnceAndEmptiesTheList(): void
    {
        $registry = new UnreleasedJobRegistry($this->path);

        $registry->record([3, 1]);
        $registry->record([1, 2]);

        $claimed = $registry->claim();
        sort($claimed);

        $this->assertSame([1, 2, 3], $claimed);
        $this->assertSame([], $registry->claim());
    }

    public function testRecord_ConcurrentWorkersLoseNoIds(): void
    {
        $code = 'require "app/Jobs/UnreleasedJobRegistry.php";'
            . '$registry = new App\Jobs\UnreleasedJobRegistry($argv[1]);'
            . 'for ($i = 0; $i < 40; $i++) { $registry->record([(int)$argv[2] * 1000 + $i]); }';

        $this->runInParallel($code, array_map(static fn (int $worker): array => [$worker], range(1, self::WORKERS)));

        $claimed = (new UnreleasedJobRegistry($this->path))->claim();

        $this->assertCount(self::WORKERS * 40, $claimed);
        $this->assertCount(self::WORKERS * 40, array_unique($claimed));
    }

    public function testClaim_ConcurrentWorkersNeverReceiveTheSameId(): void
    {
        $code = 'require "app/Jobs/UnreleasedJobRegistry.php";'
            . '$registry = new App\Jobs\UnreleasedJobRegistry($argv[1]);'
            . '$claimed = [];'
            . 'for ($i = 0; $i < 40; $i++) { $registry->record([(int)$argv[2] * 1000 + $i]); $claimed = array_merge($claimed, $registry->claim()); }'
            . 'echo implode(",", $claimed);';

        $outputs = $this->runInParallel($code, array_map(static fn (int $worker): array => [$worker], range(1, self::WORKERS)));

        $claimed = [];

        foreach ($outputs as $output) {
            if ($output !== '') {
                $claimed = array_merge($claimed, array_map('intval', explode(',', $output)));
            }
        }

        $claimed = array_merge($claimed, (new UnreleasedJobRegistry($this->path))->claim());

        $this->assertCount(self::WORKERS * 40, $claimed);
        $this->assertCount(self::WORKERS * 40, array_unique($claimed));
    }
}
