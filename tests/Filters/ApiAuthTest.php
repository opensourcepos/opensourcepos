<?php

namespace App\Tests\Filters;

use App\Filters\ApiAuth;
use App\Models\ApiKey;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\Services;

class ApiAuthTest extends CIUnitTestCase
{
    protected ApiAuth $filter;
    protected ApiKey $apiKeyModel;
    protected int $testEmployeeId = 1;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new ApiAuth();
        $this->apiKeyModel = model(ApiKey::class);
    }
    
    public function testBeforeWithNoApiKey(): void
    {
        $request = new IncomingRequest(Services::config(), Services::uri(), '');
        $request->setHeader('X-API-Key', '');
        
        $result = $this->filter->before($request);
        
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        
        $body = json_decode($result->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('API key required', $body['message']);
    }
    
    public function testBeforeWithInvalidApiKey(): void
    {
        $request = new IncomingRequest(Services::config(), Services::uri(), '');
        $request->setHeader('X-API-Key', 'ospos_invalidkey12345678901234567890123456789012345678');
        
        $result = $this->filter->before($request);
        
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        
        $body = json_decode($result->getBody(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Invalid or expired API key', $body['message']);
    }
    
    public function testBeforeWithValidApiKey(): void
    {
        $employeeId = $this->testEmployeeId;
        $rawKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key');
        
        $request = new IncomingRequest(Services::config(), Services::uri(), '');
        $request->setHeader('X-API-Key', $rawKey);
        
        $result = $this->filter->before($request);
        
        $this->assertInstanceOf(IncomingRequest::class, $result);
        $this->assertEquals($employeeId, $result->employeeId);
    }
    
    public function testAfterReturnsResponse(): void
    {
        $request = new IncomingRequest(Services::config(), Services::uri(), '');
        $response = new Response(Services::config());
        
        $result = $this->filter->after($request, $response);
        
        $this->assertInstanceOf(Response::class, $result);
    }
    
    public function testEmployeeIdSetInService(): void
    {
        $employeeId = $this->testEmployeeId;
        $rawKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key');
        
        $request = new IncomingRequest(Services::config(), Services::uri(), '');
        $request->setHeader('X-API-Key', $rawKey);
        
        $this->filter->before($request);
        
        $this->assertEquals($employeeId, Services::get('apiEmployeeId'));
    }
}