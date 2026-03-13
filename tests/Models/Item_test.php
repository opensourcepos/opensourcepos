<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\Item;

class Item_test extends CIUnitTestCase
{
    public function testSuggestionsColumnValidationRejectsSQLInjection(): void
    {
        // These malicious values should not be in the allowed columns list
        $malicious_columns = [
            'name, SLEEP(5)',
            'name; DROP TABLE items',
            "name' OR '1'='1",
            'name UNION SELECT * FROM users',
            'name--comment'
        ];

        foreach ($malicious_columns as $malicious) {
            $this->assertNotContains($malicious, Item::ALLOWED_SUGGESTIONS_COLUMNS, 
                "Malicious input should not be in allowed columns: $malicious");
        }
    }

    public function testSuggestionsColumnValidationAcceptsValidColumns(): void
    {
        // Valid columns should be preserved
        $valid_columns = ['name', 'item_number', 'description', 'cost_price', 'unit_price'];

        foreach ($valid_columns as $column) {
            $this->assertContains($column, Item::ALLOWED_SUGGESTIONS_COLUMNS, 
                "Valid column should be in allowed list: $column");
        }
    }

    public function testSuggestionsColumnValidationHandlesEmptyString(): void
    {
        // Empty string should only be allowed for second and third columns
        $this->assertContains('', Item::ALLOWED_SUGGESTIONS_COLUMNS_WITH_EMPTY, 
            'Empty string should be allowed for optional columns');
    }

    public function testSuggestionsColumnValidationConstantsAreConsistent(): void
    {
        // Ensure the constants are properly defined
        $this->assertCount(5, Item::ALLOWED_SUGGESTIONS_COLUMNS, 
            'Should have exactly 5 allowed columns');
        $this->assertCount(6, Item::ALLOWED_SUGGESTIONS_COLUMNS_WITH_EMPTY, 
            'Should have exactly 6 allowed columns including empty');
        
        // Ensure empty string is only in WITH_EMPTY variant
        $this->assertNotContains('', Item::ALLOWED_SUGGESTIONS_COLUMNS, 
            'Empty string should not be in required columns list');
    }
}
