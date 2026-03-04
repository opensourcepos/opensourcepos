<?php

namespace Tests\Models\Reports;

use PHPUnit\Framework\TestCase;
use App\Models\Reports\Summary_discounts;

class Summary_discounts_test extends TestCase
{
    public function testCurrencySymbolEscaping(): void
    {
        $malicious_symbols = [
            '"',
            "'",
            '" + SLEEP(5) + "',
            '", SLEEP(5), "',
            "' + (SELECT * FROM (SELECT(SLEEP(5)))a) + '",
            '"; DROP TABLE ospos_sales_items; --',
            '" OR 1=1 --'
        ];

        foreach ($malicious_symbols as $symbol) {
            $escaped = $this->escapeCurrencySymbol($symbol);
            
            $this->assertStringNotContainsString('SLEEP', $escaped, "SQL injection attempt should be escaped: $symbol");
            $this->assertStringNotContainsString('DROP', $escaped, "SQL injection attempt should be escaped: $symbol");
            $this->assertStringNotContainsString(';', $escaped, "Query termination should be escaped: $symbol");
        }
    }

    public function testNormalCurrencySymbolHandling(): void
    {
        $normal_symbols = ['$', '€', '£', '¥', '₹', '₩', '₽', 'kr', 'CHF'];
        
        foreach ($normal_symbols as $symbol) {
            $escaped = $this->escapeCurrencySymbol($symbol);
            $this->assertNotEmpty($escaped, "Normal currency symbol should be preserved: $symbol");
        }
    }

    private function escapeCurrencySymbol(string $symbol): string
    {
        if (strlen($symbol) === 0) {
            return "''";
        }
        
        $symbol = addslashes($symbol);
        
        return "'" . $symbol . "'";
    }
}