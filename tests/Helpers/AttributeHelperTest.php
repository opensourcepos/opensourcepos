<?php

namespace Tests\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Test suite for attribute_helper functions
 * 
 * Tests for PR #4384 attribute helper utilities
 */
class AttributeHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('attribute');
    }

    /**
     * Test getAttributeDataType returns correct column names
     * 
     * @return void
     */
    public function testGetAttributeDataTypeForText(): void
    {
        $this->assertEquals('attribute_value', getAttributeDataType('TEXT'));
    }

    /**
     * Test getAttributeDataType for DECIMAL type
     * 
     * @return void
     */
    public function testGetAttributeDataTypeForDecimal(): void
    {
        $this->assertEquals('attribute_decimal', getAttributeDataType('DECIMAL'));
    }

    /**
     * Test getAttributeDataType for DATE type
     * 
     * @return void
     */
    public function testGetAttributeDataTypeForDate(): void
    {
        $this->assertEquals('attribute_date', getAttributeDataType('DATE'));
    }

    /**
     * Test getAttributeDataType for DROPDOWN type
     * 
     * @return void
     */
    public function testGetAttributeDataTypeForDropdown(): void
    {
        // Note: DROPDOWN is a special case that uses attribute_value
        // This test verifies the expected behavior
        $this->assertEquals('attribute_value', getAttributeDataType('DROPDOWN'));
    }

    /**
     * Test getAttributeDataType for invalid type returns fallback
     * 
     * @return void
     */
    public function testGetAttributeDataTypeForInvalidType(): void
    {
        // Invalid types should return 'attribute_value' as fallback
        $this->assertEquals('attribute_value', getAttributeDataType('INVALID_TYPE'));
    }

    /**
     * Test getAttributeDataType for checkbox type
     * 
     * @return void
     */
    public function testGetAttributeDataTypeForCheckbox(): void
    {
        // CHECKBOX values are stored as '0' or '1' in attribute_value
        $this->assertEquals('attribute_value', getAttributeDataType('CHECKBOX'));
    }

    /**
     * Test that validateAttributeValueType throws exception for invalid type
     * 
     * @return void
     */
    public function testValidateAttributeValueTypeInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        validateAttributeValueType('INVALID_TYPE');
    }

    /**
     * Test that validateAttributeValueType does not throw for valid types
     * 
     * @return void
     */
    public function testValidateAttributeValueTypeValidText(): void
    {
        // Should not throw exception
        validateAttributeValueType('attribute_value');
    }

    /**
     * Test that validateAttributeValueType does not throw for decimal type
     * 
     * @return void
     */
    public function testValidateAttributeValueTypeValidDecimal(): void
    {
        // Should not throw exception
        validateAttributeValueType('attribute_decimal');
    }

    /**
     * Test that validateAttributeValueType does not throw for date type
     * 
     * @return void
     */
    public function testValidateAttributeValueTypeValidDate(): void
    {
        // Should not throw exception
        validateAttributeValueType('attribute_date');
    }
}