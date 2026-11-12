<?php

namespace Tests\Logging;

use App\Logging\JobsFileHandler;
use CodeIgniter\Test\CIUnitTestCase;

class JobsFileHandlerTest extends CIUnitTestCase
{
    private string $logDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logDir = sys_get_temp_dir() . '/ospos_log_test_' . uniqid() . '/';
        mkdir($this->logDir, 0755, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (glob($this->logDir . '*') as $file) {
            @unlink($file);
        }
        @rmdir($this->logDir);
    }

    private function makeHandler(string $queueName, string $ext = ''): JobsFileHandler
    {
        return new JobsFileHandler([
            'handles'         => ['error', 'info', 'debug', 'notice', 'warning', 'critical', 'alert', 'emergency'],
            'path'            => $this->logDir,
            'fileExtension'   => $ext,
            'filePermissions' => 0660,
        ], $queueName);
    }

    public function testHandle_WritesToPerQueueFile(): void
    {
        $handler = $this->makeHandler('default');
        $handler->setDateFormat('Y-m-d H:i:s')->handle('info', 'hello from default');

        $expected = $this->logDir . 'jobsLog-default-' . date('Y-m-d') . '.';
        $files = glob($this->logDir . 'jobsLog-default-*');

        $this->assertCount(1, $files);
        $this->assertStringContainsString('jobsLog-default-', $files[0]);
        $this->assertStringContainsString('hello from default', file_get_contents($files[0]));
    }

    public function testHandle_SeparatesQueuesByFileName(): void
    {
        $this->makeHandler('imports')->setDateFormat('Y-m-d H:i:s')->handle('info', 'row saved');
        $this->makeHandler('default')->setDateFormat('Y-m-d H:i:s')->handle('error', 'job failed');

        $importFiles = glob($this->logDir . 'jobsLog-imports-*');
        $defaultFiles = glob($this->logDir . 'jobsLog-default-*');

        $this->assertCount(1, $importFiles);
        $this->assertCount(1, $defaultFiles);
        $this->assertStringContainsString('row saved', file_get_contents($importFiles[0]));
        $this->assertStringContainsString('job failed', file_get_contents($defaultFiles[0]));
        $this->assertStringNotContainsString('row saved', file_get_contents($defaultFiles[0]));
    }

    public function testHandle_PrefixesLevelUppercase(): void
    {
        $this->makeHandler('default')->setDateFormat('Y-m-d H:i:s')->handle('error', 'something broke');

        $file = glob($this->logDir . 'jobsLog-default-*')[0];
        $this->assertMatchesRegularExpression('/^ERROR - /', file_get_contents($file));
    }

    public function testHandle_AppendsOnSubsequentWrites(): void
    {
        $handler = $this->makeHandler('default');
        $handler->setDateFormat('Y-m-d H:i:s')->handle('info', 'first');
        $handler->setDateFormat('Y-m-d H:i:s')->handle('info', 'second');

        $file = glob($this->logDir . 'jobsLog-default-*')[0];
        $content = file_get_contents($file);

        $this->assertStringContainsString('first', $content);
        $this->assertStringContainsString('second', $content);
    }

    public function testHandle_ReturnsTrue(): void
    {
        $handler = $this->makeHandler('default');
        $result = $handler->setDateFormat('Y-m-d H:i:s')->handle('info', 'ok');

        $this->assertTrue($result);
    }

    public function testHandle_UsesPhpHeaderWhenExtensionIsPhp(): void
    {
        $handler = $this->makeHandler('default', 'php');
        $handler->setDateFormat('Y-m-d H:i:s')->handle('info', 'guarded');

        $file = glob($this->logDir . 'jobsLog-default-*')[0];
        $this->assertStringContainsString("<?php defined('SYSTEMPATH')", file_get_contents($file));
    }
}
