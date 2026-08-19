<?php

namespace Tests\Filters;

use App\Filters\Throttle;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

class ThrottleTest extends CIUnitTestCase
{
    private Throttle $filter;
    private array $usedKeys = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->filter = new Throttle();
    }

    protected function tearDown(): void
    {
        $throttler = Services::throttler();

        foreach ($this->usedKeys as $key) {
            $throttler->remove($key);
        }

        parent::tearDown();
    }

    private function makeRequest(string $method, string $ip, ?string $username = null): IncomingRequest
    {
        $request = $this->createMock(IncomingRequest::class);

        $request->method('getMethod')->willReturn($method);
        $request->method('getIPAddress')->willReturn($ip);
        $request->method('getPost')->with('username')->willReturn($username);

        $this->usedKeys[] = 'login-ip-' . md5($ip);

        if ($username !== null && $username !== '') {
            $this->usedKeys[] = 'login-user-' . md5(strtolower($username));
        }

        return $request;
    }

    public function testGetRequestsAreNotThrottled(): void
    {
        $request = $this->makeRequest('GET', '203.0.113.1', 'admin');

        $result = $this->filter->before($request);

        $this->assertNull($result);
    }

    public function testAllowsAttemptsUnderCapacity(): void
    {
        $ip = '203.0.113.2';

        for ($i = 0; $i < 5; $i++) {
            $request = $this->makeRequest('POST', $ip, 'employee1');
            $result  = $this->filter->before($request);

            $this->assertNull($result, "Attempt {$i} should not be throttled");
        }
    }

    public function testBlocksAfterCapacityExceededByIp(): void
    {
        $ip = '203.0.113.3';

        for ($i = 0; $i < 5; $i++) {
            $this->filter->before($this->makeRequest('POST', $ip, "user{$i}"));
        }

        $result = $this->filter->before($this->makeRequest('POST', $ip, 'user-final'));

        $this->assertNotNull($result);
        $this->assertSame(429, $result->getStatusCode());
    }

    public function testBlocksAfterCapacityExceededByUsername(): void
    {
        $username = 'sameuser';

        for ($i = 0; $i < 5; $i++) {
            $this->filter->before($this->makeRequest('POST', "203.0.113.{$i}", $username));
        }

        $result = $this->filter->before($this->makeRequest('POST', '203.0.113.99', $username));

        $this->assertNotNull($result);
        $this->assertSame(429, $result->getStatusCode());
    }

    public function testThrottledResponseIsJsonWithMessage(): void
    {
        $ip = '203.0.113.4';

        for ($i = 0; $i < 5; $i++) {
            $this->filter->before($this->makeRequest('POST', $ip, 'someone'));
        }

        $result = $this->filter->before($this->makeRequest('POST', $ip, 'someone'));

        $body = json_decode($result->getJSON(), true);

        $this->assertFalse($body['success']);
        $this->assertSame(lang('Login.too_many_attempts'), $body['message']);
    }

    public function testHandlesIpv6AddressWithoutThrowing(): void
    {
        $ip = '::1';

        for ($i = 0; $i < 5; $i++) {
            $result = $this->filter->before($this->makeRequest('POST', $ip, "user{$i}"));
            $this->assertNull($result, "Attempt {$i} should not be throttled");
        }

        $result = $this->filter->before($this->makeRequest('POST', $ip, 'user-final'));

        $this->assertNotNull($result);
        $this->assertSame(429, $result->getStatusCode());
    }

    public function testMissingUsernameOnlyThrottlesByIp(): void
    {
        $ip = '203.0.113.5';

        for ($i = 0; $i < 5; $i++) {
            $this->filter->before($this->makeRequest('POST', $ip, null));
        }

        $result = $this->filter->before($this->makeRequest('POST', $ip, null));

        $this->assertNotNull($result);
        $this->assertSame(429, $result->getStatusCode());
    }
}
