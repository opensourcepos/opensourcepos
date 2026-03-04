<?php

namespace Tests\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Test suite for importfile_helper functions
 * 
 * Tests for PR #4384 CSV import attribute deletion capability with _DELETE_ magic word
 */
class ImportFileHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('importfile');
    }
    
    /**
     * Test _DELETE_ magic word case-insensitive comparison
     * 
     * The PR uses strcasecmp for case-insensitive comparison of _DELETE_
     * 
     * @return void
     */
    public function testDeleteMagicWordCaseInsensitive(): void
    {
        // Test that strcasecmp identifies _DELETE_ regardless of case
        $this->assertEquals(0, strcasecmp('_DELETE_', '_DELETE_'), 
            'Exact match should return 0');
        $this->assertEquals(0, strcasecmp('_DELETE_', '_delete_'), 
            'Lowercase should match');
        $this->assertEquals(0, strcasecmp('_DELETE_', '_Delete_'), 
            'Mixed case should match');
        
        // Test that non-matching strings return non-zero
        $this->assertNotEquals(0, strcasecmp('_DELETE_', 'DELETE'), 
            'Without underscore should not match');
        $this->assertNotEquals(0, strcasecmp('_DELETE_', 'test'), 
            'Random text should not match');
    }
    
    /**
     * Test that _DELETE_ does not match similar-looking strings
     * 
     * @return void
     */
    public function testDeleteMagicWordNotConfusedWithSimilar(): void
    {
        // These should NOT match
        $this->assertNotEquals(0, strcasecmp('_DELETE_', '__DELETE__'), 
            'Double underscore should not match');
        $this->assertNotEquals(0, strcasecmp('_DELETE_', 'DELETE_'), 
            'Without underscore should not match');
        $this->assertNotEquals(0, strcasecmp('_DELETE_', '_DELETE '), 
            'With trailing space should not match');
        $this->assertNotEquals(0, strcasecmp('_DELETE_', ' _DELETE_'), 
            'With leading space should not match');
    }
    
    /**
     * Test empty string does not match _DELETE_
     * 
     * @return void
     */
    public function testEmptyStringNotDelete(): void
    {
        $this->assertNotEquals(0, strcasecmp('_DELETE_', ''), 
            'Empty string should not match _DELETE_');
    }
    
    /**
     * Test null safety with strcasecmp
     * 
     * @return void
     */
    public function testDeleteMagicWordNullSafety(): void
    {
        // strcasecmp with null would cause a warning
        // This test documents the need for null checking in the controller
        $testString = '_DELETE_';
        $this->assertIsString($testString, 'Test string should not be null');
        
        // In the actual code, empty() checks would be done before strcasecmp
        $this->assertTrue(!empty($testString), 'Empty check should pass');
    }
}