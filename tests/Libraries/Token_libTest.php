<?php

namespace Tests\Libraries;

use App\Libraries\Token_lib;
use CodeIgniter\Test\CIUnitTestCase;

class Token_libTest extends CIUnitTestCase
{
    private Token_lib $tokenLib;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenLib = new Token_lib();
    }

    public function testRenderReturnsInputStringWhenNoTokens(): void
    {
        $input = 'Hello World';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertEquals('Hello World', $result);
    }

    public function testRenderHandlesStringWithPercentNotInDateFormat(): void
    {
        $input = 'Discount: 50%';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertStringContainsString('50%', $result);
        $this->assertNotEmpty($result);
    }

    public function testRenderHandlesInvalidDateFormatPercentDashPercent(): void
    {
        $input = '%-%-%';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertNotEquals('', $result);
    }

    public function testRenderHandlesInvalidDateFormatPercentYPercentQPercentBad(): void
    {
        $input = '%Y-%q-%bad';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
    }

    public function testRenderHandlesStringWithPercentAPercent(): void
    {
        $input = '%a%';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
    }

    public function testRenderHandlesExtremelyLongString(): void
    {
        $input = str_repeat('a', 10000);
        $result = $this->tokenLib->render($input, [], false);
        $this->assertEquals(str_repeat('a', 10000), $result);
    }

    public function testRenderHandlesStringWithMultiplePercentSymbols(): void
    {
        $input = 'Sale: 25% off, then another 10%';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertStringContainsString('25%', $result);
        $this->assertStringContainsString('10%', $result);
    }

    public function testRenderHandlesStringWithOnlyPercentSymbol(): void
    {
        $input = '%';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertEquals('%', $result);
    }

    public function testRenderPreservesTextWithValidDateTokensAndNoOtherTokens(): void
    {
        $input = 'Date: %Y-%m-%d';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertStringContainsString('Date:', $result);
    }

    public function testRenderHandlesEmptyString(): void
    {
        $input = '';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertEquals('', $result);
    }

    public function testScanExtractsTokens(): void
    {
        $result = $this->tokenLib->scan('Hello {customer} and {invoice}');
        $this->assertArrayHasKey('customer', $result);
        $this->assertArrayHasKey('invoice', $result);
    }

    public function testScanExtractsTokensWithLength(): void
    {
        $result = $this->tokenLib->scan('Invoice: {invoice:10}');
        $this->assertArrayHasKey('invoice', $result);
        $this->assertArrayHasKey('10', $result['invoice']);
    }

    public function testScanReturnsEmptyArrayForNoTokens(): void
    {
        $result = $this->tokenLib->scan('Hello World');
        $this->assertEmpty($result);
    }

    public function testRenderHandlesConsecutivePercentSigns(): void
    {
        $input = 'Progress: 100%% complete';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('complete', $result);
    }

    public function testRenderHandlesEscapedPercentSigns(): void
    {
        $input = 'Value: %%';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
    }

    public function testRenderHandlesUnclosedBraces(): void
    {
        $input = "Invoice {CO Date: %Y-%m-%d";
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
    }

    public function testRenderHandlesUnopenedBraces(): void
    {
        $input = "Invoice CO} Date: %Y-%m-%d";
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
    }

    public function testRenderHandlesVeryLongStringWithDate(): void
    {
        $input = str_repeat('buffer ', 500) . '%Y-%m-%d Invoice' . str_repeat('buffer ', 500);
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('buffer', $result);
    }

    public function testRenderHandlesMultipleDates(): void
    {
        $input = '%Y-%m-%d Invoice - %Y-%m-%d';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
    }

    public function testRenderHandlesValidYearFormat(): void
    {
        $input = 'Year: %Y';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertMatchesRegularExpression('/Year: \d{4}/', $result);
    }

    public function testRenderHandlesValidMonthFormat(): void
    {
        $input = 'Month: %m';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertMatchesRegularExpression('/Month: \d{2}/', $result);
    }

    public function testRenderHandlesValidDayFormat(): void
    {
        $input = 'Day: %d';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertMatchesRegularExpression('/Day: \d{2}/', $result);
    }

    public function testRenderHandlesFullDateFormat(): void
    {
        $input = 'Date: %Y-%m-%d';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertMatchesRegularExpression('/Date: \d{4}-\d{2}-\d{2}/', $result);
    }

    public function testRenderHandlesPercentB(): void
    {
        $input = 'Month: %B';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Month:', $result);
        $this->assertNotEquals('Month: %B', $result);
    }

    public function testRenderHandlesPercentA(): void
    {
        $input = 'Day: %A';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Day:', $result);
        $this->assertNotEquals('Day: %A', $result);
    }

    public function testRenderHandlesComplexPercentFormat(): void
    {
        $input = 'Report: %Y-%m-%d at %H:%M:%S';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Report:', $result);
    }

    public function testRenderDoesNotReplaceInvalidFormatSpecifiers(): void
    {
        $input = 'Test: %q invalid %j valid';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertStringContainsString('%q', $result);
        $this->assertStringContainsString('invalid', $result);
    }

    public function testRenderReplacesTimezoneFormat(): void
    {
        $input = 'Timezone: %z';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Timezone:', $result);
    }

    public function testScanWorksWithMixedContent(): void
    {
        $result = $this->tokenLib->scan('Text {token1} more %Y-%m-%d text {token2:5} end');
        $this->assertArrayHasKey('token1', $result);
        $this->assertArrayHasKey('token2', $result);
    }

    public function testRenderReplacesCompositeDirectivePercentF(): void
    {
        $input = 'Date: %F';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString('%F', $result);
        $this->assertMatchesRegularExpression('/Date: \d{4}-\d{2}-\d{2}/', $result);
    }

    public function testRenderReplacesCompositeDirectivePercentD(): void
    {
        $input = 'Date: %D';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString('%D', $result);
        $this->assertMatchesRegularExpression('/Date: \d{2}\/\d{2}\/\d{2}/', $result);
    }

    public function testRenderHandlesPercentT(): void
    {
        $input = 'Time: %T';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString('%T', $result);
        $this->assertMatchesRegularExpression('/Time: \d{2}:\d{2}:\d{2}/', $result);
    }

    public function testRenderHandlesPercentR(): void
    {
        $input = 'Time: %R';
        $result = $this->tokenLib->render($input, [], false);
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString('%R', $result);
        $this->assertMatchesRegularExpression('/Time: \d{2}:\d{2}/', $result);
    }
}