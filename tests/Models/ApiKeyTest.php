<?php

namespace App\Tests\Models;

use App\Models\ApiKey;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class ApiKeyTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    
    protected $migrate = true;
    protected $migrateOnly = ['api_keys'];
    protected $refresh = true;
    
    protected ApiKey $apiKeyModel;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->apiKeyModel = new ApiKey();
    }
    
    public function testGenerateKey(): void
    {
        $employeeId = 1;
        $name = 'Test API Key';
        
        $rawKey = $this->apiKeyModel->generateKey($employeeId, $name);
        
        $this->assertNotFalse($rawKey);
        $this->assertStringStartsWith('ospos_', $rawKey);
        $this->assertEquals(70, strlen($rawKey)); // ospos_ prefix (6 chars) + 64 hex chars
        
        $keyInDb = $this->apiKeyModel->where('employee_id', $employeeId)
            ->where('name', $name)
            ->first();
        
        $this->assertNotNull($keyInDb);
        $this->assertEquals(substr($rawKey, 0, 12), $keyInDb->key_prefix);
        $this->assertEquals(hash('sha256', $rawKey), $keyInDb->key_hash);
    }
    
    public function testValidateKeySuccess(): void
    {
        $employeeId = 1;
        $rawKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key');
        
        $validatedEmployeeId = $this->apiKeyModel->validateKey($rawKey);
        
        $this->assertEquals($employeeId, $validatedEmployeeId);
    }
    
    public function testValidateKeyInvalidFormat(): void
    {
        $result = $this->apiKeyModel->validateKey('invalid_key');
        $this->assertFalse($result);
        
        $result = $this->apiKeyModel->validateKey('ospos_short');
        $this->assertFalse($result);
        
        $result = $this->apiKeyModel->validateKey('otherprefix_' . str_repeat('a', 64));
        $this->assertFalse($result);
    }
    
    public function testValidateKeyDisabled(): void
    {
        $employeeId = 1;
        $rawKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key');
        
        $keyRecord = $this->apiKeyModel->where('employee_id', $employeeId)->first();
        $this->apiKeyModel->update($keyRecord->api_key_id, ['disabled' => 1]);
        
        $result = $this->apiKeyModel->validateKey($rawKey);
        $this->assertFalse($result);
    }
    
    public function testValidateKeyExpired(): void
    {
        $employeeId = 1;
        $expiresAt = date('Y-m-d H:i:s', strtotime('-1 day'));
        $rawKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key', $expiresAt);
        
        $result = $this->apiKeyModel->validateKey($rawKey);
        $this->assertFalse($result);
    }
    
    public function testValidateKeyNotExpired(): void
    {
        $employeeId = 1;
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
        $rawKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key', $expiresAt);
        
        $result = $this->apiKeyModel->validateKey($rawKey);
        $this->assertEquals($employeeId, $result);
    }
    
    public function testGetKeysForEmployee(): void
    {
        $employeeId = 1;
        
        $this->apiKeyModel->generateKey($employeeId, 'Key 1');
        $this->apiKeyModel->generateKey($employeeId, 'Key 2');
        $this->apiKeyModel->generateKey($employeeId, 'Key 3');
        
        $keys = $this->apiKeyModel->getKeysForEmployee($employeeId);
        
        $this->assertCount(3, $keys);
    }
    
    public function testRevokeKey(): void
    {
        $employeeId = 1;
        $rawKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key');
        
        $keyRecord = $this->apiKeyModel->where('employee_id', $employeeId)->first();
        
        $result = $this->apiKeyModel->revokeKey($keyRecord->api_key_id, $employeeId);
        $this->assertTrue($result);
        
        $updatedKey = $this->apiKeyModel->find($keyRecord->api_key_id);
        $this->assertEquals(1, $updatedKey->disabled);
        
        $validateResult = $this->apiKeyModel->validateKey($rawKey);
        $this->assertFalse($validateResult);
    }
    
    public function testRevokeKeyWrongEmployee(): void
    {
        $employeeId = 1;
        $this->apiKeyModel->generateKey($employeeId, 'Test Key');
        
        $keyRecord = $this->apiKeyModel->where('employee_id', $employeeId)->first();
        
        $result = $this->apiKeyModel->revokeKey($keyRecord->api_key_id, 999);
        
        $updatedKey = $this->apiKeyModel->find($keyRecord->api_key_id);
        $this->assertEquals(0, $updatedKey->disabled);
    }
    
    public function testRegenerateKey(): void
    {
        $employeeId = 1;
        $oldKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key');
        
        $keyRecord = $this->apiKeyModel->where('employee_id', $employeeId)->first();
        $oldKeyId = $keyRecord->api_key_id;
        
        $newKey = $this->apiKeyModel->regenerateKey($oldKeyId, $employeeId);
        
        $this->assertNotFalse($newKey);
        $this->assertNotEquals($oldKey, $newKey);
        
        $oldKeyValid = $this->apiKeyModel->validateKey($oldKey);
        $this->assertFalse($oldKeyValid);
        
        $newKeyValid = $this->apiKeyModel->validateKey($newKey);
        $this->assertEquals($employeeId, $newKeyValid);
        
        $oldKeyExists = $this->apiKeyModel->find($oldKeyId);
        $this->assertNull($oldKeyExists);
    }
    
    public function testKeyHashNotReversible(): void
    {
        $employeeId = 1;
        $rawKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key');
        
        $keyRecord = $this->apiKeyModel->where('employee_id', $employeeId)->first();
        
        $this->assertNotEquals($rawKey, $keyRecord->key_hash);
        $this->assertEquals(64, strlen($keyRecord->key_hash));
    }
    
    public function testLastUsedUpdatesOnValidation(): void
    {
        $employeeId = 1;
        $rawKey = $this->apiKeyModel->generateKey($employeeId, 'Test Key');
        
        $keyRecord = $this->apiKeyModel->where('employee_id', $employeeId)->first();
        $this->assertNull($keyRecord->last_used);
        
        sleep(1);
        
        $this->apiKeyModel->validateKey($rawKey);
        
        $keyRecord = $this->apiKeyModel->find($keyRecord->api_key_id);
        $this->assertNotNull($keyRecord->last_used);
    }
}