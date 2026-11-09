<?php

namespace Tests\Events;

use App\Events\Method;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MethodTest extends CIUnitTestCase
{
    private array $originalServer;
    private array $originalPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalServer = $_SERVER;
        $this->originalPost   = $_POST;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        $_POST   = $this->originalPost;

        parent::tearDown();
    }

    private function runValidateMethodInSubprocess(string $requestUri, string $requestMethod = 'GET'): string
    {
        $methodFile = APPPATH . 'Events/Method.php';
        $code       = sprintf(
            'require %s; $_SERVER["REQUEST_URI"] = %s; $_SERVER["REQUEST_METHOD"] = %s; $_POST = []; App\Events\Method::validate_method(); echo "ALLOWED";',
            var_export($methodFile, true),
            var_export($requestUri, true),
            var_export($requestMethod, true),
        );

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open([PHP_BINARY, '-r', $code], $descriptorSpec, $pipes);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return (string) $output;
    }

    public function testValidateMethodRejectsMixedCaseDeleteGetRequest(): void
    {
        $output = $this->runValidateMethodInSubprocess('/items/Delete/1', 'GET');

        $this->assertSame('Method not allowed', $output);
    }

    public function testValidateMethodRejectsMixedCaseLogoutGetRequest(): void
    {
        $output = $this->runValidateMethodInSubprocess('/home/Logout', 'GET');

        $this->assertSame('Method not allowed', $output);
    }

    public function testValidateMethodRejectsUrlEncodedActionGetRequest(): void
    {
        $output = $this->runValidateMethodInSubprocess('/items/%64elete/1', 'GET');

        $this->assertSame('Method not allowed', $output);
    }

    public function testValidateMethodRejectsSaveAndRemoveGetRequests(): void
    {
        $this->assertSame('Method not allowed', $this->runValidateMethodInSubprocess('/items/Save/1', 'GET'));
        $this->assertSame('Method not allowed', $this->runValidateMethodInSubprocess('/items/Remove?id=1', 'GET'));
    }

    public function testValidateMethodAcceptsPostRequestsToProtectedEndpoints(): void
    {
        foreach (['/items/Delete/1', '/home/Logout', '/items/save/1', '/items/remove'] as $uri) {
            $_SERVER['REQUEST_URI']    = $uri;
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST                     = [];

            ob_start();
            Method::validate_method();
            $output = ob_get_clean();

            $this->assertSame('', $output);
        }
    }

    public function testValidateMethodAcceptsGetRequestsToNonMatchingPaths(): void
    {
        foreach (['/items/deletion/1', '/items/removed_items', '/items/view/1'] as $uri) {
            $_SERVER['REQUEST_URI']    = $uri;
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_POST                     = [];

            ob_start();
            Method::validate_method();
            $output = ob_get_clean();

            $this->assertSame('', $output);
        }
    }
}
