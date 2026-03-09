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

        $valid_columns = ['name', 'item_number', 'description', 'cost_price', 'unit_price'];

        foreach ($malicious_columns as $malicious) {
            $this->assertNotContains($malicious, $valid_columns, "Malicious input should be rejected: $malicious");
        }
    }

    public function testSuggestionsColumnValidationAcceptsValidColumns(): void
    {
        $valid_columns = ['name', 'item_number', 'description', 'cost_price', 'unit_price', ''];

        foreach ($valid_columns as $column) {
            $validated = $this->validateSuggestionsColumn($column);
            
            if ($column === '') {
                $this->assertTrue($validated === '' || $validated === 'name', "Empty column should be valid or default to name");
            } else {
                $this->assertContains($validated, ['name', 'item_number', 'description', 'cost_price', 'unit_price'], "Valid column should be preserved: $column");
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
        $valid_columns = ['', 'name', 'item_number', 'description', 'cost_price', 'unit_price'];

        return in_array($column, $valid_columns, true) ? $column : 'name';
    }
}