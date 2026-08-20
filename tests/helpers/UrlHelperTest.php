<?php

    use PHPUnit\Framework\TestCase;

    class UrlHelperTest extends TestCase
    {
        protected function setUp(): void
        {
            // Include the url_helper.php file
            require_once __DIR__ . '/../../app/Helpers/url_helper.php';
        }

        public function testBase64urlEncode(): void
        {
            $data = 'Test data';
            $encoded = base64url_encode($data);

            // Assert that the encoded string is URL-safe
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9\-_]+$/', $encoded);

            // Assert that decoding the encoded string returns the original data
            $decoded = base64url_decode($encoded);
            $this->assertEquals($data, $decoded);
        }
    }
