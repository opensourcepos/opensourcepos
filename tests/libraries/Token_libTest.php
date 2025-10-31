<?php

use app\Libraries\Token_lib;
use PHPUnit\Framework\TestCase;

    class Token_libTest extends TestCase
    {
        private token_lib $tokenLib;

        private function testHelper(string $tokenText): void
        {
            $tokens = [];
            error_log("-----\nTesting string '$tokenText' with tokens " . implode(", ", $tokens));

            $currentResult = $this->tokenLib->render($tokenText, $tokens);
            error_log("current: $currentResult\n");

            $newResult = $this->tokenLib->renderUpdated($tokenText, $tokens);
            error_log("new: $newResult\n\-----\n");

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
            error_log("-----\nTesting string '$tokenText' with tokens " . implode(", ", $tokens));

            $currentResult = $this->tokenLib->render($tokenText, $tokens);
            error_log("current: $currentResult\n");

            $newResult = $this->tokenLib->renderUpdated($tokenText, $tokens);
            error_log("new: $newResult\n\-----\n");

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

        public function testHandlesEmptyTokenTree(): void
        {
            $this->testHelper('No tokens here');
        }

        public function testHandlesNonexistentTokens(): void
        {
            $this->testHelper('{INVALID}');
        }

        public function testComplexInvoiceTemplate(): void
        {
            $this->testHelper('Invoice #{CO:6} - %B %d, %Y - Customer: {CUSTOMER}');
        }
    }
