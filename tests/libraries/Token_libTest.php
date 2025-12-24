<?php

use app\Libraries\Token_lib;
use PHPUnit\Framework\TestCase;

    class Token_libTest extends TestCase
    {
        private token_lib $tokenLib;

        private function testHelper(string $tokenText, bool $save = true): void
        {
            $tokens = [];
            error_log("Testing string '$tokenText' with tokens " . implode(", ", $tokens));

            $currentResult = $this->tokenLib->render($tokenText, $tokens, $save);
            error_log("current: \"$currentResult\"");

            $newResult = $this->tokenLib->renderUpdated($tokenText, $tokens, $save);
            error_log("new: \"$newResult\"\n");

            $this->assertEquals($currentResult, $newResult);
        }

        protected function setUp(): void {
            require_once __DIR__ . "/../../app/Libraries/Token_lib.php";
            $this->tokenLib = new Token_lib();
        }

        public function testReplacesSimpleTokens() {
            $this->testHelper('{CO}');
        }

        public function testReplacesTokensWithLength() {
            $this->testHelper('{CO:5}');
        }

        public function testReplacesMultipleTokens() {
            $this->testHelper('Invoice {CO} - {DATE}');
        }

        public function testHandlesPercentTokensWithoutBraceTokens(): void
        {
            $this->testHelper('Date: %Y-%m-%d');
        }

        public function testHandlesPercentTokensWithBraceTokens(): void
        {
            $tokenText = 'Invoice {CO} on %mm-%dd-%yyyy';
            $tokens = [];
            error_log("Testing string '$tokenText' with tokens " . implode(", ", $tokens));

            $currentResult = $this->tokenLib->render($tokenText, $tokens);
            error_log("current: \"$currentResult\"");

            $newResult = $this->tokenLib->renderUpdated($tokenText, $tokens);
            error_log("new: \"$newResult\"\n");

            $this->assertNotEquals($currentResult, $newResult);
        }

        public function testIgnoresTextWithoutPercentSign(): void
        {
            $this->testHelper('Plain text');
        }

        public function testHandlesEmptyString(): void
        {
            $this->testHelper('');
        }

        public function testHandlesNonexistentTokens(): void
        {
            $this->testHelper('{INVALID}');
        }

        public function testHandlesComplexInvoiceTemplate(): void
        {
            $this->testHelper('Invoice #{CO:6} - %B %d, %Y - Customer: {CUSTOMER}');
        }

        public function testHandlesNewLines(): void
        {
            $this->testHelper("Invoice {CO}\nDate: %Y-%m-%d");
            $this->testHelper("Invoice {CO}\r\nDate: %Y-%m-%d");
            $this->testHelper("Invoice {CO}\rDate: %Y-%m-%d");
        }

        public function testHandlesTabs(): void
        {
            $this->testHelper("Invoice\t{CO}\tCustomer\t{CUSTOMER}");
        }

        public function testHandlesNewLinesAndTabs(): void
        {
            $this->testHelper("Invoice\n\t{CO}\n\tCustomer\n\t{CUSTOMER}");
        }

        public function testHandlesSpecialCharacters(): void
        {
            $this->testHelper("Invoice #{CO} @ $100 & tax!");
        }

        public function testHandlesUnicode()
        {
            $this->testHelper("客户 {CUSTOMER} - 发票 {CO}");
        }

        public function testHandlesNestedBraces(): void
        {
            $this->testHelper("Invoice {{CO}} Date: %Y-%m-%d");
        }

        public function testHandlesUnclosedBraces(): void
        {
            $this->testHelper("Invoice {CO Date: %Y-%m-%d");
        }

        public function testHandlesUnopenedBraces(): void
        {
            $this->testHelper("Invoice CO} Date: %Y-%m-%d");
        }

        public function testHandlesDateAtStart(): void
        {
            $this->testHelper('%Y-%m-%d Invoice {CO}');
        }

        public function testHandlesHtmlTags(): void
        {
            // if your IDE complains about CO not being defined, ignore it
            $this->testHelper(htmlentities("<script>{CO}</script>"));
        }

        public function testHandlesSqlInjectionAttempt(): void
        {
            $this->testHelper("'; DROP TABLE--{CO}");
        }

        public function testHandlesVeryLongString(): void
        {
            // TODO: This test still fails
            $this->testHelper(str_repeat('buffer ', 500) . '%Y-%m-%d Invoice {CO}' . str_repeat('buffer ', 500));
        }

        public function testHandlesMultipleDates(): void
        {
            // TODO: This test still fails
            $this->testHelper('%Y-%m-%d Invoice {CO} - %Y-%m-%d');
        }

        public function testHandlesNotDatePercentTokens(): void
        {
            // TODO: This test still fails
            $this->testHelper('Discount: 50%');
        }

        public function testHandlesBadDateFormats(): void
        {
            // TODO: This test still fails
            $this->testHelper("%-%-%");
            $this->testHelper("%Y-%q-%bad");
            $this->testHelper("%a%");
        }

        public function testSaveParameter(): void
        {
            $this->testHelper('{CO}', false);
            $this->testHelper('Plain text', false);
            $this->testHelper('Date: %Y-%m-%d', false);
            $this->testHelper('Invoice #{CO:6} - %B %d, %Y - Customer: {CUSTOMER}', false);
        }
    }
