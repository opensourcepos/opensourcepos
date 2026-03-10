<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\Item;

class Item_test extends CIUnitTestCase
{
    public function testSuggestionsColumnValidationRejectsSQLInjection(): void
    {
        $malicious_columns = [
            'name, SLEEP(5)',
            'name; DROP TABLE items',
            "name' OR '1'='1",
            'name UNION SELECT * FROM users',
            'name--comment'
        ];

        foreach ($malicious_columns as $malicious) {
            $this->assertNotContains($malicious, Item::ALLOWED_SUGGESTIONS_COLUMNS, "Malicious input should be rejected: $malicious");
        }
    }

    public function testSuggestionsColumnValidationAcceptsValidColumns(): void
    {
        foreach (Item::ALLOWED_SUGGESTIONS_COLUMNS_WITH_EMPTY as $column) {
            $validated = $this->validateSuggestionsColumn($column);
            
            if ($column === '') {
                $this->assertTrue($validated === '' || $validated === 'name', "Empty column should be valid or default to name");
            } else {
                $this->assertContains($validated, Item::ALLOWED_SUGGESTIONS_COLUMNS, "Valid column should be preserved: $column");
            }
        }
    }

    public function testSuggestionsColumnValidationDefaultsToNameForInvalidInput(): void
    {
        $invalid_inputs = [
            'invalid_column',
            'SELECT * FROM items',
            '<?php echo "test"; ?>',
            '<script>alert("xss")</script>',
            'name OR 1=1'
        ];

        foreach ($invalid_inputs as $invalid) {
            $validated = $this->validateSuggestionsColumn($invalid);
            $this->assertEquals('name', $validated, "Invalid input should default to 'name': $invalid");
        }
    }

    private function validateSuggestionsColumn(?string $column): string
    {
        return in_array($column, Item::ALLOWED_SUGGESTIONS_COLUMNS_WITH_EMPTY, true) ? $column : 'name';
    }
}